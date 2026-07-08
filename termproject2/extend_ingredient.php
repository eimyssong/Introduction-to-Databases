<?php
require_once "config.php";

// 1. 로그인 여부 및 역할 확인 (유저만 가능)
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'user') {
    die("권한이 없습니다.");
}

if (!isset($_GET['id'])) {
    die("잘못된 접근입니다.");
}

$ing_id = $_GET['id'];
$student_id = $_SESSION['user']['student_id'];

// 2. 식재료 정보 조회 (본인 소유 확인 및 조건 검사)
$stmt = $pdo->prepare("SELECT * FROM Ingredient WHERE ing_id = ?");
$stmt->execute([$ing_id]);
$row = $stmt->fetch();

if (!$row) {
    die("존재하지 않는 식재료입니다.");
}

// 3. 본인 소유 확인
if ($row['user_student_id'] != $student_id) {
    echo "<script>alert('본인의 식재료만 연장할 수 있습니다.'); history.back();</script>";
    exit;
}

// 4. 조건 검사: (D-day <= 7) 또는 (상태 == '폐기대상')
$today = new DateTime();
$today->setTime(0, 0, 0);

$exp = new DateTime($row['EXP_DATETIME']);
$exp->setTime(0, 0, 0);

$interval = $today->diff($exp);
$diff_days = $interval->days;       // 일수 차이
$is_past = ($interval->invert === 1); // 유통기한 지남(과거)

// 남은 기간이 7일 이하인지 확인 (과거 날짜는 무조건 포함)
$is_urgent = ($is_past || $diff_days <= 7);
$is_discard = ($row['state'] === '폐기대상');

if ($is_urgent || $is_discard) {
    // 5. 연장 실행 (유통기한 +7일, 상태 -> '정상'으로 복구)
    // 폐기대상이었던 물건도 연장하면 다시 '정상' 상태가 되어야 관리자가 폐기하지 않음
    $updateStmt = $pdo->prepare("
        UPDATE Ingredient 
        SET EXP_DATETIME = DATE_ADD(EXP_DATETIME, INTERVAL 14 DAY),
            state = '정상' 
        WHERE ing_id = ?
    ");
    $updateStmt->execute([$ing_id]);

    echo "<script>alert('유통기한이 14일 연장되었습니다.'); location.href='index.php';</script>";
} else {
    echo "<script>alert('연장 가능한 대상이 아닙니다. (D-Day 7일 이하 또는 폐기대상만 가능)'); history.back();</script>";
}
?>