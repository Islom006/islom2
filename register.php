
<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "db.php";

// Faqat POST so'rovlarini qabul qilamiz
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Bu so'rov usuli qo'llab-quvvatlanmaydi."
    ]);
    exit;
}

// JavaScript yuborgan JSON ma'lumotlarini o'qiymiz
$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Ma'lumotlar noto'g'ri yuborildi."
    ]);
    exit;
}

// Formadan kelgan ma'lumotlar
$firstName = trim($data["firstName"] ?? "");
$lastName = trim($data["lastName"] ?? "");
$email = trim($data["email"] ?? "");
$phone = trim($data["phone"] ?? "");
$password = $data["password"] ?? "";
$passwordConfirm = $data["passwordConfirm"] ?? "";

// Maydonlarni tekshiramiz
if (
    $firstName === "" ||
    $lastName === "" ||
    $email === "" ||
    $phone === "" ||
    $password === "" ||
    $passwordConfirm === ""
) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Barcha maydonlarni to'ldiring."
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Email manzili noto'g'ri."
    ]);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Parol kamida 8 ta belgidan iborat bo'lishi kerak."
    ]);
    exit;
}

if ($password !== $passwordConfirm) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Parollar bir-biriga mos emas."
    ]);
    exit;
}

try {
    // Email avval ro'yxatdan o'tganmi?
    $check = $pdo->prepare(
        "SELECT id FROM users WHERE email = ? LIMIT 1"
    );
    $check->execute([$email]);

    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Bu email allaqachon ro'yxatdan o'tgan."
        ]);
        exit;
    }

    // Parolni ochiq matn ko'rinishida saqlamaymiz
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $fullName = $firstName . " " . $lastName;

    // Yangi foydalanuvchini bazaga yozamiz
    $insert = $pdo->prepare(
        "INSERT INTO users
        (full_name, email, phone, password_hash, role)
        VALUES (?, ?, ?, ?, 'customer')"
    );

    $insert->execute([
        $fullName,
        $email,
        $phone,
        $passwordHash
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Ro'yxatdan muvaffaqiyatli o'tdingiz!"
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Serverda xatolik yuz berdi. Qayta urinib ko'ring."
    ]);
}