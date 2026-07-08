<?php
require_once 'config.php';
$logged = isset($_SESSION['user']);
$is_admin = ($logged && $_SESSION['role'] === 'advisor');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>냉장고 식재료 관리</title>
</head>
<body>
<h1>DGIST 기숙사 냉장고 관리 시스템</h1>

<?php if(!$logged): ?>
  <p><a href="login.php">로그인</a></p>
<?php else: ?>
  <p>안녕하세요, <?= htmlspecialchars($_SESSION['user']['user_name'] ?? $_SESSION['user']['advisor_name']) ?> 님! 
  (<?= $_SESSION['role']==='advisor' ? '관리자' : '사용자' ?>)</p>
  <p><a href="logout.php">로그아웃</a></p>
  <p><a href="add_ingredient.php">➕ 식재료 등록</a></p>
  <?php if($is_admin): ?>
      <p><a href="?filter=expired">⚠️ 폐기대상 보기</a> | <a href="index.php">전체 보기</a></p>
  <?php endif; ?>
<?php endif; ?>

<h3>식재료 목록</h3>
<table border="1" cellpadding="6">
  <tr>
    <th>ID</th><th>이름</th><th>제조일</th><th>유통기한</th><th>D-Day</th><th>보관위치</th><th>상태</th><th>등록자</th>
    <?php if($logged): ?><th>관리</th><?php endif; ?>
  </tr>
  <?php
  $query = "SELECT I.ing_id, I.ing_name, I.MFG_DATETIME, I.EXP_DATETIME, I.storage_loc, I.state, U.user_name
            FROM Ingredient I JOIN user U ON I.user_student_id = U.student_id";
  if ($is_admin && isset($_GET['filter']) && $_GET['filter'] === 'expired') {
      $query .= " WHERE I.state = '폐기대상'";
  }
  $query .= " ORDER BY I.EXP_DATETIME ASC";
  $stmt = $pdo->query($query);

  while ($row = $stmt->fetch()) {
      // D-Day 계산
      $today = new DateTime();
      $exp = new DateTime($row['EXP_DATETIME']);
      $diff = $today->diff($exp)->days;
      $dlabel = ($exp < $today) ? "D+" . $diff : "D-" . $diff;
      if ($exp < $today) $dlabel = "<span style='color:red'>$dlabel</span>";

      echo "<tr>
              <td>{$row['ing_id']}</td>
              <td>{$row['ing_name']}</td>
              <td>{$row['MFG_DATETIME']}</td>
              <td>{$row['EXP_DATETIME']}</td>
              <td>$dlabel</td>
              <td>{$row['storage_loc']}</td>
              <td>{$row['state']}</td>
              <td>{$row['user_name']}</td>";

      if ($logged) {
          $can_delete = (!$is_admin && $_SESSION['user']['student_id'] == $row['user_student_id']) || $is_admin;
          echo "<td>";
          if ($is_admin || $_SESSION['role'] === 'user') {
              echo "<a href='delete_ingredient.php?id={$row['ing_id']}' onclick='return confirm(\"정말 삭제하시겠습니까?\")'>삭제</a>";
          }
          echo "</td>";
      }
      echo "</tr>";
  }
  ?>
</table>
</body>
</html>
