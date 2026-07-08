<?php
require_once "config.php";
if ($_SESSION['role'] !== 'advisor') exit;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name = $_POST['fridge_name'];
    $stmt = $pdo->prepare("INSERT INTO Fridge(fridge_name) VALUES(?)");
    $stmt->execute([$name]);
    header("Location: fridge_manage.php");
    exit;
}
?>
<form method="post">
  냉장고 이름: <input name="fridge_name" required><br>
  <button>등록</button>
</form>
