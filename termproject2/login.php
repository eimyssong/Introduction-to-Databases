<?php
require_once 'config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id']; // 학번 입력
    $password = $_POST['password'];

    // 사용자 로그인 시도 (학번 기준)
    $stmt = $pdo->prepare("SELECT * FROM user WHERE student_id = ? AND password = ?");
    $stmt->execute([$student_id, $password]);
    $user = $stmt->fetch();

    // 관리자 로그인 시도 (기존처럼 이름 기준)
    $stmt2 = $pdo->prepare("SELECT * FROM advisor WHERE advisor_id = ? AND advisor_password = ?");
    $stmt2->execute([$student_id, $password]); 
    $advisor = $stmt2->fetch();

    if ($user) {
        $_SESSION['user'] = $user;
        $_SESSION['role'] = 'user';
        header("Location: index.php");
        exit;
    } elseif ($advisor) {
        $_SESSION['user'] = $advisor;
        $_SESSION['role'] = 'advisor';
        header("Location: index.php");
        exit;
    } else {
        $error = "로그인 실패: 학번 또는 비밀번호가 잘못되었습니다.";
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head><meta charset="UTF-8"><title>로그인</title></head>
<body>
<h2>로그인</h2>
<form method="post">
  학번: <input type="text" name="student_id" required><br>
  비밀번호: <input type="password" name="password" required><br>
  <button type="submit">로그인</button>
</form>
<p style="color:red"><?= htmlspecialchars($error) ?></p>
</body>
</html>
