<?php
require_once "config.php";

// 1. 관리자 권한 확인
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'advisor') {
    die("관리자만 접근 가능합니다.");
}

// 2. 필수 파라미터 확인
if (!isset($_GET['fridge_id']) || !isset($_GET['action'])) {
    die("잘못된 요청입니다.");
}

$fridge_id = $_GET['fridge_id'];
$action = $_GET['action'];
$advisor_id = $_SESSION['user']['advisor_id'];

try {
    $pdo->beginTransaction(); // 트랜잭션 시작 (안전한 처리를 위해)

    if ($action === 'dispose_expired') {
        // [기능 1] 유통기한 만료(D+1 이상) -> '폐기대상'으로 변경
        
        // 1-1. 폐기 기록(DisposeHistory)에 먼저 남기기 (상태가 아직 '폐기대상'이 아니고, 유통기한 지난 것들)
        $historySql = "
            INSERT INTO DisposeHistory (ing_id, advisor_id, dispose_date)
            SELECT ing_id, ?, NOW()
            FROM Ingredient
            WHERE fridge_id = ? 
              AND EXP_DATETIME < CURDATE() 
              AND state != '폐기대상'
        ";
        $stmt = $pdo->prepare($historySql);
        $stmt->execute([$advisor_id, $fridge_id]);
        
        // 1-2. 식재료 상태 업데이트 ('정상' -> '폐기대상')
        $updateSql = "
            UPDATE Ingredient
            SET state = '폐기대상'
            WHERE fridge_id = ? 
              AND EXP_DATETIME < CURDATE() 
              AND state != '폐기대상'
        ";
        $stmt = $pdo->prepare($updateSql);
        $stmt->execute([$fridge_id]);
        
        $count = $stmt->rowCount();
        $msg = "총 {$count}개의 만료된 식재료를 폐기 처리했습니다.";

    } elseif ($action === 'delete_old') {
        // [기능 2] 폐기 상태이고 D+7 이상 경과한 것 -> 완전 삭제
        
        $deleteSql = "
            DELETE FROM Ingredient
            WHERE fridge_id = ?
              AND state = '폐기대상'
              AND EXP_DATETIME <= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ";
        $stmt = $pdo->prepare($deleteSql);
        $stmt->execute([$fridge_id]);
        
        $count = $stmt->rowCount();
        $msg = "폐기 상태이며 7일이 지난 식재료 {$count}개를 DB에서 삭제했습니다.";
    }

    $pdo->commit(); // 완료
    
    // 처리 후 알림 및 복귀
    echo "<script>alert('$msg'); location.href='fridge_manage.php';</script>";

} catch (Exception $e) {
    $pdo->rollBack(); // 에러 발생 시 취소
    die("오류 발생: " . $e->getMessage());
}
?>