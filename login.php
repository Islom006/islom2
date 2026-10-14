
<?php
session_start();

header("Content-Type: application/json; charset=UTF-8");
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Noto'g'ri so'rov."
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if ($email === "" || $password === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Email va parolni kiriting."
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT id, full_name, email, password_hash, role
         FROM users
         WHERE email = ?
         LIMIT 1"
    );

    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user["password_hash"])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Email yoki parol noto'g'ri."
        ]);
        exit;
    }

    session_regenerate_id(true);

    $_SESSION["user_id"] = $user["id"];
    $_SESSION["full_name"] = $user["full_name"];
    $_SESSION["email"] = $user["email"];
    $_SESSION["role"] = $user["role"];

    echo json_encode([
        "success" => true,
        "message" => "Tizimga muvaffaqiyatli kirdingiz!",
        "user" => [
            "full_name" => $user["full_name"],
            "email" => $user["email"]
        ]
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Serverda xatolik yuz berdi."
    ]);
}