<?php
require_once 'config.php';

$logged = isset($_SESSION['user']);
$is_admin = ($logged && $_SESSION['role'] === 'advisor');
$is_user = ($logged && $_SESSION['role'] === 'user');

// 현재 사용자의 학번 (필터링에 사용됨)
$current_student_id = $is_user ? ($_SESSION['user']['student_id'] ?? null) : null;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>냉장고 식재료 관리</title>
<style>
    table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
    th, td { padding: 8px 12px; text-align: center; }
    th { background-color: #f2f2f2; }
    .disabled-btn { color: #ccc; cursor: not-allowed; text-decoration: none; }
    .extend-btn { color: green; font-weight: bold; text-decoration: none; }
    /* 안내 메시지 스타일 */
    .login-info { background-color: #f7f7f7; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-top: 20px; }
</style>
</head>
<body>

<h1>DGIST 기숙사 냉장고 관리 시스템</h1>

<?php if(!$logged): ?>
  
  <p><a href="login.php">로그인 페이지로 이동</a></p>

  <div class="login-info">
      <h3>로그인 안내</h3>
      <p>
          아이디는 학번입니다.<br>
          비밀번호를 잊었거나, 시스템에 등록되어 있지 않으면 
          학생생활관자치위원회(dormcommittee@dgist.ac.kr)로 문의 주십시오.
      </p>
  </div>

<?php else: ?>

  <p>
    안녕하세요, 
    <strong><?= htmlspecialchars($_SESSION['user']['user_name'] ?? $_SESSION['user']['advisor_name']) ?></strong> 님!  
    (<?= $_SESSION['role']==='advisor' ? '관리자' : '사용자' ?>)
  </p>

  <p><a href="logout.php">로그아웃</a></p>

  <?php if($is_user): ?>
      <p>
          <a href="add_ingredient.php">➕ 식재료 등록</a> | 
          <a href="index.php?filter=mine">👤 내 식재료 보기</a> |
          <a href="index.php">전체 식재료 보기</a>
      </p>
  <?php endif; ?>

  <?php if($is_admin): ?>
      <p>
        <a href="?filter=expired">⚠️ 폐기대상 보기</a> | 
        <a href="index.php">전체 보기</a> | 
        <a href="fridge_manage.php">📦 냉장고 관리</a>
      </p>
  <?php endif; ?>

<?php
// 냉장고별 정렬 쿼리
$query = "
  SELECT 
    I.ing_id, I.ing_name, I.MFG_DATETIME, I.EXP_DATETIME,
    I.storage_loc, I.state, I.user_student_id,
    U.user_name, 
    F.fridge_id, F.fridge_name
  FROM Ingredient I
  JOIN user U ON I.user_student_id = U.student_id
  LEFT JOIN Fridge F ON I.fridge_id = F.fridge_id
";

$params = []; // 쿼리 바인딩 파라미터 배열

// 1. 관리자 전용 필터 (폐기대상)
if ($is_admin && isset($_GET['filter']) && $_GET['filter'] === 'expired') {
    $query .= " WHERE I.state = '폐기대상'";
}

// 2. 사용자 전용 필터 (내 식재료 보기)
if ($is_user && isset($_GET['filter']) && $_GET['filter'] === 'mine' && $current_student_id) {
    if (strpos($query, 'WHERE') === false) {
        $query .= " WHERE I.user_student_id = ?";
    } else {
        $query .= " AND I.user_student_id = ?";
    }
    $params[] = $current_student_id;
}


$query .= " ORDER BY F.fridge_name ASC, I.EXP_DATETIME ASC";

// 쿼리 실행
$stmt = $pdo->prepare($query);
$stmt->execute($params);


$current_fridge = null;

while ($row = $stmt->fetch()) {

    $fridge_label = htmlspecialchars($row['fridge_name']);

    // 새로운 냉장고가 나오면 테이블 시작
    if ($current_fridge !== $row['fridge_id']) {
        if ($current_fridge !== null) echo "</table>"; // 이전 냉장고 테이블 닫기

        echo "<h3>냉장고: {$fridge_label}</h3>";
        echo "<table border='1'>
                <tr>
                    <th>ID</th>
                    <th>이름</th>
                    <th>제조일</th>
                    <th>유통기한</th>
                    <th>D-Day</th>
                    <th>보관위치</th>
                    <th>상태</th>
                    <th>등록자</th>";
        if($logged) echo "<th>관리</th>";
        echo "</tr>";

        $current_fridge = $row['fridge_id'];
    }

    // 날짜 계산
    $today = new DateTime();
    $today->setTime(0,0,0);
    $exp = new DateTime($row['EXP_DATETIME']);
    $exp->setTime(0,0,0);
    $interval = $today->diff($exp);
    $diff_days = $interval->days;
    $is_past = ($interval->invert === 1);

    $dlabel = $is_past ? "<span style='color:red; font-weight:bold;'>D+{$diff_days}</span>" : "D-{$diff_days}";

    echo "<tr>
            <td>{$row['ing_id']}</td>
            <td>" . htmlspecialchars($row['ing_name']) . "</td>
            <td>{$row['MFG_DATETIME']}</td>
            <td>{$row['EXP_DATETIME']}</td>
            <td>{$dlabel}</td>
            <td>" . htmlspecialchars($row['storage_loc']) . "</td>
            <td style='" . ($row['state']==='폐기대상'?'color:red;':'') . "'>{$row['state']}</td>
            <td>" . htmlspecialchars($row['user_name']) . "</td>";

    // 관리 버튼
    echo "<td>";
    if($logged){
        if($_SESSION['role']==='user' && $_SESSION['user']['student_id']==$row['user_student_id']){
            $is_urgent = ($is_past || $diff_days <=7);
            $is_discard = ($row['state']==='폐기대상');
            
            // [연장 버튼]
            if($is_urgent || $is_discard){
                echo "<a href='extend_ingredient.php?id={$row['ing_id']}' onclick='return confirm(\"유통기한을 14일 연장하시겠습니까?\")' class='extend-btn'>연장</a> | ";
            } else {
                echo "<span class='disabled-btn' title='D-Day 7일 이하일 때만 연장 가능'>연장</span> | ";
            }
            
            // [삭제 버튼]
            echo "<a href='delete_ingredient.php?id={$row['ing_id']}' onclick='return confirm(\"정말 삭제하시겠습니까?\")'>삭제</a>";
        }

        if($is_admin){
            if($is_past && $row['state']!=='폐기대상'){
                echo "<a href='dispose.php?id={$row['ing_id']}' onclick='return confirm(\"폐기 처리하시겠습니까?\")'>폐기</a> | ";
            }
            $can_delete_admin = ($row['state']==='폐기대상' && $is_past && $diff_days>=7);
            if($can_delete_admin){
                echo "<a href='delete_ingredient.php?id={$row['ing_id']}' onclick='return confirm(\"DB에서 완전히 삭제하시겠습니까?\")' style='color:red;font-weight:bold;'>삭제</a>";
            } else {
                echo "<span class='disabled-btn' title='폐기대상이고 D+7일 이상이어야 삭제 가능'>삭제</span>";
            }
        }
    }
    echo "</td></tr>";
}

// 마지막 테이블 닫기
if($current_fridge!==null) echo "</table>";
?>

<?php endif; ?> </body>
</html>