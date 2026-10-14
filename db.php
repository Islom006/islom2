
<?php

// 1. Ma'lumotlar bazasi sozlamalari
$host = "localhost";
$dbname = "savdo_db";
$username = "root";
$password = ""; // XAMPP'da standart holatda bo'sh

// 2. PHP va MySQL o'rtasida ulanish yaratish
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // Xatolarni istisno sifatida qaytarish
    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    // Natijalarni assotsiativ massiv sifatida olish
    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {
    // Texnik xatoni foydalanuvchiga oshkor qilmaymiz
    error_log($e->getMessage());
    exit("Ma'lumotlar bazasiga ulanish amalga oshmadi.");
}

// 3. Sessiya: foydalanuvchi holatini eslab qolish
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
