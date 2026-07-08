<?php
require_once "config.php";
if ($_SESSION['role'] !== 'advisor') exit;

$id = $_GET['id'];

$stmt = $pdo->prepare("INSERT INTO DisposeHistory(ing_id, advisor_id) VALUES(?,?)");
$stmt->execute([$id, $_SESSION['user']['advisor_id']]);

$pdo->prepare("UPDATE Ingredient SET state='폐기대상' WHERE ing_id=?")
    ->execute([$id]);

header("Location: index.php");
exit;
?>
