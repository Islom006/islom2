
<?php
session_start();
require_once "db.php";

if (empty($_SESSION["user_id"])) {
    header("Location: 114.html");
    exit;
}

$stmt = $pdo->prepare(
    "SELECT full_name, email, role
     FROM users
     WHERE id = ?
     LIMIT 1"
);
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user["role"] !== "admin") {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="uz">
    <head>
        <meta charset="UTF-8">
        <title>Kirish taqiqlangan</title>
    </head>
    <body>
        <h1>403 — Kirish taqiqlangan</h1>
        <p>Bu sahifaga faqat admin kira oladi.</p>
        <a href="index.html">Bosh sahifa</a>
    </body>
    </html>
    <?php
    exit;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$totalUsers = (int)$pdo
    ->query("SELECT COUNT(*) FROM users")
    ->fetchColumn();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin panel</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #222;
        }
        header {
            background: #172033;
            color: white;
            padding: 22px;
        }
        main {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 18px;
        }
        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 16px #0000000c;
        }
        .number {
            font-size: 34px;
            font-weight: bold;
            margin-top: 10px;
        }
        .btn {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 8px;
            background: #1f5eff;
            color: white;
            text-decoration: none;
            margin: 5px 8px 5px 0;
        }
        .logout { background: #d93636; }
    </style>
</head>
<body>
    <header>
        <h1>Admin panel</h1>
        <p>Assalomu alaykum, <?= h($user["full_name"]) ?>!</p>
    </header>

    <main>
        <section class="card">
            <h2>Boshqaruv paneli</h2>
            <p>Bu bo'limga faqat admin rolidagi foydalanuvchilar kira oladi.</p>
            <p>Email: <?= h($user["email"]) ?></p>
            <p>Rol: Admin</p>
        </section>

        <section class="card">
            <h2>Foydalanuvchilar</h2>
            <p>Saytda ro'yxatdan o'tgan foydalanuvchilar soni:</p>
            <div class="number"><?= $totalUsers ?></div>
        </section>

        <section class="card">
            <h2>Tezkor havolalar</h2>
            <a class="btn" href="index.html">Savdo saytiga o'tish</a>
            <a class="btn" href="profile.php">Mening profilim</a>
            <a class="btn logout" href="logout.php">Chiqish</a>
            
            <a class="btn" href="admin_products.php">Mahsulotlarni boshqarish</a>
        </section>
    </main>
</body>
</html>