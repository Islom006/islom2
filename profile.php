
<?php
session_start();
require_once "db.php";

if (empty($_SESSION["user_id"])) {
    header("Location: 114.html");
    exit;
}

$stmt = $pdo->prepare(
    "SELECT full_name, email, phone, role
     FROM users
     WHERE id = ?
     LIMIT 1"
);
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION = [];
    session_destroy();
    header("Location: 114.html");
    exit;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mening profilim</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px 16px;
            color: #222;
        }
        .card {
            max-width: 520px;
            margin: 30px auto;
            background: white;
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 4px 18px #00000010;
        }
        .item {
            padding: 14px 0;
            border-bottom: 1px solid #eee;
            overflow-wrap: anywhere;
        }
        .label { color: #666; font-size: 14px; }
        .value { margin-top: 6px; font-weight: bold; }
        .btn {
            display: inline-block;
            margin: 16px 8px 0 0;
            padding: 12px 18px;
            background: #1f5eff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
        .logout { background: #d93636; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Mening profilim</h1>

        <div class="item">
            <div class="label">Ism-familiya</div>
            <div class="value"><?= h($user["full_name"]) ?></div>
        </div>

        <div class="item">
            <div class="label">Email</div>
            <div class="value"><?= h($user["email"]) ?></div>
        </div>

        <div class="item">
            <div class="label">Telefon</div>
            <div class="value">
                <?= h($user["phone"] ?: "Kiritilmagan") ?>
            </div>
        </div>

        <div class="item">
            <div class="label">Akkaunt roli</div>
            <div class="value">
                <?= $user["role"] === "admin" ? "Admin" : "Foydalanuvchi" ?>
            </div>
        </div>

        <?php if ($user["role"] === "admin"): ?>
            <a class="btn" href="admin.php">Admin panel</a>
        <?php endif; ?>

        <a class="btn" href="index.html">Bosh sahifa</a>
        <a class="btn logout" href="logout.php">Chiqish</a>
    </main>
</body>
</html>