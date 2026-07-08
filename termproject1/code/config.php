<?php
    $DB_HOST = 'database-1.czcqyqay64h4.ap-northeast-2.rds.amazonaws.com';
    $DB_USER = 'admin';
    $DB_PASS = ''; //revise
    $DB_NAME = 'DormitoryDB';

    try {
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        die("❌ DB 연결 실패: " . $e->getMessage());
    }

    session_start();
?>
