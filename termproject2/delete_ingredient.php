<?php
require_once "config.php";

// 1. 로그인 여부 확인
if (!isset($_SESSION['user'])) {
    die("로그인이 필요합니다.");
}

// 2. 삭제할 식재료 ID 확인
if (!isset($_GET['id'])) {
    die("잘못된 접근입니다.");
}

$id = $_GET['id'];
$role = $_SESSION['role'];
$user_id = $_SESSION['user']['student_id'] ?? null; // 관리자는 student_id가 없음

// 3. 해당 식재료 정보 조회 (삭제 권한 확인용)
$stmt = $pdo->prepare("SELECT * FROM Ingredient WHERE ing_id = ?");
$stmt->execute([$id]);
$ingredient = $stmt->fetch();

if (!$ingredient) {
    die("존재하지 않는 식재료입니다.");
}

// 4. 권한 검사 및 삭제 로직
$can_delete = false;

if ($role === 'user') {
    // [사용자] 본인이 등록한 식재료인지 확인
    if ($ingredient['user_student_id'] == $user_id) {
        $can_delete = true;
    } else {
        echo "<script>alert('본인이 등록한 식재료만 삭제할 수 있습니다.'); history.back();</script>";
        exit;
    }

} elseif ($role === 'advisor') {
    // [관리자] '폐기대상' + 유통기한 7일 경과 확인
    $exp_date = new DateTime($ingredient['EXP_DATETIME']);
    $today = new DateTime();
    $diff = $today->diff($exp_date);
    
    // 유통기한이 지났고(invert=1), 차이가 7일 이상인지 확인
    $is_expired_7days = ($diff->invert == 1 && $diff->days >= 7);
    
    if ($ingredient['state'] === '폐기대상' && $is_expired_7days) {
        $can_delete = true;
    } else {
        echo "<script>alert('관리자는 폐기대상이며 유통기한이 7일 이상 지난 항목만 삭제할 수 있습니다.'); history.back();</script>";
        exit;
    }
}

// 5. 삭제 실행
if ($can_delete) {
    // 만약 DisposeHistory 테이블에 외래 키 제약 조건(ON DELETE CASCADE)이 걸려있다면,
    // Ingredient 테이블에서만 삭제하면 관련 기록도 자동 삭제됩니다.
    // 그렇지 않다면 DisposeHistory를 먼저 지워야 할 수도 있습니다.
    
    $del_stmt = $pdo->prepare("DELETE FROM Ingredient WHERE ing_id = ?");
    $del_stmt->execute([$id]);

    // 삭제 성공 시 목록 페이지로 이동
    header("Location: index.php");
    exit;
} else {
    die("삭제 권한이 없습니다.");
}
?>