<?php
    // 로컬 환경 설정 (XAMPP 기준)
    $DB_HOST = '127.0.0.1'; // 또는 'localhost'
    $DB_USER = 'root';      // 로컬 DB 기본 관리자 ID
    $DB_PASS = '';          
    $DB_NAME = 'DormitoryDB_ref';

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