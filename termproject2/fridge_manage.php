<?php
require_once 'config.php';

// 관리자 권한 확인
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'advisor') {
    echo "<script>alert('관리자만 접근 가능합니다.'); location.href='login.php';</script>";
    exit;
}

// 냉장고 목록 조회
$stmt = $pdo->query("SELECT * FROM Fridge ORDER BY fridge_id ASC");
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>냉장고 관리</title>
<style>
    table { border-collapse: collapse; width: 100%; }
    th, td { padding: 8px 12px; text-align: center; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    .btn { padding: 4px 8px; text-decoration: none; border-radius: 4px; font-size: 12px; color: white; display: inline-block; margin: 2px; }
    .btn-dispose { background-color: #ff9800; } /* 주황색 */
    .btn-delete { background-color: #f44336; }  /* 빨간색 */
    .btn-del-fridge { color: red; text-decoration: underline; }
</style>
</head>
<body>

<h2>📦 냉장고 관리</h2>

<p>
    <a href="index.php">🏠 메인으로</a> | 
    <a href="fridge_new.php">➕ 새 냉장고 추가</a>
</p>

<table>
  <tr>
    <th>ID</th>
    <th>이름</th>
    <th width="40%">식재료 일괄 정리</th>
    <th>냉장고 삭제</th>
  </tr>

<?php foreach ($rows as $f): ?>
  <tr>
    <td><?= $f['fridge_id'] ?></td>
    <td><?= htmlspecialchars($f['fridge_name']) ?></td>
    
    <td>
        <a href="fridge_bulk_action.php?fridge_id=<?= $f['fridge_id'] ?>&action=dispose_expired" 
           class="btn btn-dispose"
           onclick="return confirm('<?= $f['fridge_name'] ?>의 유통기한이 지난 모든 식재료를 \'폐기대상\'으로 변경하시겠습니까?')">
           🗑️ 만료 항목 폐기 (D+1)
        </a>

        <a href="fridge_bulk_action.php?fridge_id=<?= $f['fridge_id'] ?>&action=delete_old" 
           class="btn btn-delete"
           onclick="return confirm('<?= $f['fridge_name'] ?>에서 \'폐기대상\'이고 7일 이상 지난 항목을 완전히 삭제하시겠습니까? \n(복구할 수 없습니다)')">
           ❌ 오래된 항목 삭제 (D+7)
        </a>
    </td>

    <td>
        <a href="fridge_delete.php?id=<?= $f['fridge_id'] ?>" 
           class="btn-del-fridge"
           onclick="return confirm('냉장고를 삭제하면 안에 있는 식재료도 모두 삭제됩니다. 계속하시겠습니까?')">
           삭제
        </a>
    </td>
  </tr>
<?php endforeach; ?>

</table>

<p style="color:gray; font-size:0.9em; margin-top:20px;">
    * <strong>만료 항목 폐기:</strong> 유통기한이 어제까지였던(D+1 이상) '정상' 식재료를 일괄적으로 '폐기대상' 상태로 변경합니다.<br>
    * <strong>오래된 항목 삭제:</strong> 이미 '폐기대상' 상태이며 유통기한이 7일 이상 지난(D+7 이상) 식재료를 DB에서 영구 삭제합니다.
</p>

</body>
</html>