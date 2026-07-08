<?php
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// 냉장고 목록 불러오기
$stmt = $pdo->query("SELECT * FROM Fridge ORDER BY fridge_id ASC");
$fridges = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['ing_name'];
    $mfg = $_POST['MFG_DATETIME'];
    $exp = $_POST['EXP_DATETIME'];
    $loc = $_POST['storage_loc'];
    $fridge_id = $_POST['fridge_id'];

    $state = "정상";   // ★ 상태는 항상 정상으로 고정 ★

    $student_id = $_SESSION['user']['student_id'];

    // 식재료 등록
    $stmt = $pdo->prepare("
        INSERT INTO Ingredient 
            (ing_name, MFG_DATETIME, storage_loc, EXP_DATETIME, state, user_student_id, fridge_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $mfg, $loc, $exp, $state, $student_id, $fridge_id]);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>식재료 등록</title>
</head>
<body>

<h2>식재료 등록</h2>

<form method="post">

  이름: 
  <input type="text" name="ing_name" required><br>

  제조일: 
  <input type="date" name="MFG_DATETIME" required><br>

  유통기한: 
  <input type="date" name="EXP_DATETIME" required><br>

  보관 위치: 
  <input type="text" name="storage_loc" required><br>

  <!-- 상태 입력 제거: 항상 정상으로 저장됨 -->

  냉장고 선택:
  <select name="fridge_id" required>
    <?php foreach ($fridges as $f): ?>
      <option value="<?= $f['fridge_id'] ?>">
        <?= htmlspecialchars($f['fridge_name']) ?> 
      </option>
    <?php endforeach; ?>
  </select><br><br>

  <button type="submit">등록</button>

</form>

<p><a href="index.php">목록으로 돌아가기</a></p>

</body>
</html>
