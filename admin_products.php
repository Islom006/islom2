
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();
require_once "db.php";

/* 1. Admin huquqini tekshirish */
if (empty($_SESSION["user_id"])) {
    header("Location: 114.html");
    exit;
}

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION["user_id"]]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser || $currentUser["role"] !== "admin") {
    http_response_code(403);
    exit("403 - Bu sahifaga faqat admin kira oladi.");
}

/* 2. Yordamchi funksiyalar */
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function goBack($message, $type = "success") {
    $_SESSION["product_flash"] = [
        "message" => $message,
        "type" => $type
    ];
    header("Location: admin_products.php");
    exit;
}

/* Mahsulot nomidan unikal slug yaratish */
function makeSlug($pdo, $name, $excludeId = null) {
    $text = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $name);

    if ($text === false) {
        $text = $name;
    }

    $slug = strtolower($text);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');

    if ($slug === "") {
        $slug = "product";
    }

    $base = substr($slug, 0, 190);
    $slug = $base;
    $number = 2;

    while (true) {
        if ($excludeId !== null) {
            $stmt = $pdo->prepare(
                "SELECT id FROM products WHERE slug = ? AND id != ? LIMIT 1"
            );
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $pdo->prepare(
                "SELECT id FROM products WHERE slug = ? LIMIT 1"
            );
            $stmt->execute([$slug]);
        }

        if (!$stmt->fetch()) {
            return $slug;
        }

        $suffix = "-" . $number;
        $slug = substr($base, 0, 220 - strlen($suffix)) . $suffix;
        $number++;
    }
}

/* Rasmni xavfsiz o'chirish */
function deleteProductImage($path) {
    if (
        !$path ||
        !str_starts_with($path, "uploads/products/")
    ) {
        return;
    }

    $base = realpath(__DIR__ . "/uploads/products");
    $file = realpath(__DIR__ . "/" . $path);

    if (
        $base &&
        $file &&
        is_file($file) &&
        str_starts_with($file, $base . DIRECTORY_SEPARATOR)
    ) {
        unlink($file);
    }
}

/* Rasm yuklash */
function uploadProductImage() {
    if (
        !isset($_FILES["image"]) ||
        $_FILES["image"]["error"] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    $file = $_FILES["image"];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Rasmni yuklashda xatolik.");
    }

    if ($file["size"] > 3 * 1024 * 1024) {
        throw new RuntimeException("Rasm 3 MB dan oshmasligi kerak.");
    }

    if (!is_uploaded_file($file["tmp_name"])) {
        throw new RuntimeException("Yuklangan fayl noto'g'ri.");
    }

    $info = getimagesize($file["tmp_name"]);

    if ($info === false) {
        throw new RuntimeException("Faqat rasm yuklang.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp",
        "image/gif"  => "gif"
    ];

    if (
        !isset($allowed[$mime]) ||
        $info["mime"] !== $mime
    ) {
        throw new RuntimeException(
            "JPG, PNG, WEBP yoki GIF rasm tanlang."
        );
    }

    $directory = __DIR__ . "/uploads/products";

    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        throw new RuntimeException("Rasm papkasini yaratib bo'lmadi.");
    }

    $filename = bin2hex(random_bytes(16)) . "." . $allowed[$mime];

    if (!move_uploaded_file(
        $file["tmp_name"],
        $directory . "/" . $filename
    )) {
        throw new RuntimeException("Rasmni saqlab bo'lmadi.");
    }

    return "uploads/products/" . $filename;
}

/* 3. CSRF token */
if (empty($_SESSION["product_csrf"])) {
    $_SESSION["product_csrf"] = bin2hex(random_bytes(32));
}

/* 4. Qo'shish, tahrirlash va o'chirish */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["product_csrf"], $token)
    ) {
        http_response_code(403);
        exit("Xavfsizlik tekshiruvi xatosi. Sahifani yangilang.");
    }

    $action = $_POST["action"] ?? "";

    try {
        /* Mahsulotni o'chirish */
        if ($action === "delete") {
            $id = filter_var(
                $_POST["id"] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id || $id < 1) {
                throw new RuntimeException("Mahsulot ID noto'g'ri.");
            }

            $stmt = $pdo->prepare(
                "SELECT image FROM products WHERE id = ?"
            );
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new RuntimeException("Mahsulot topilmadi.");
            }

            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);

            deleteProductImage($product["image"]);

            goBack("Mahsulot muvaffaqiyatli o'chirildi.");
        }

        if (!in_array($action, ["create", "update"], true)) {
            throw new RuntimeException("Noma'lum amal.");
        }

        $id = filter_var(
            $_POST["id"] ?? null,
            FILTER_VALIDATE_INT
        );

        $name = trim($_POST["name"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $category = trim($_POST["category"] ?? "");
        $priceInput = trim($_POST["price"] ?? "");
        $oldPriceInput = trim($_POST["old_price"] ?? "");
        $stock = filter_var(
            $_POST["stock"] ?? null,
            FILTER_VALIDATE_INT
        );
        $isActive = (string)($_POST["is_active"] ?? "1");

        if ($name === "" || mb_strlen($name) > 200) {
            throw new RuntimeException(
                "Mahsulot nomini kiriting (200 belgigacha)."
            );
        }

        if (
            $priceInput === "" ||
            !is_numeric($priceInput) ||
            !is_finite((float)$priceInput) ||
            (float)$priceInput <= 0 ||
            (float)$priceInput > 9999999999.99
        ) {
            throw new RuntimeException("Mahsulot narxini to'g'ri kiriting.");
        }

        if ($oldPriceInput !== "" && (
            !is_numeric($oldPriceInput) ||
            !is_finite((float)$oldPriceInput) ||
            (float)$oldPriceInput <= 0 ||
            (float)$oldPriceInput > 9999999999.99
        )) {
            throw new RuntimeException("Eski narx noto'g'ri.");
        }

        if ($stock === false || $stock < 0) {
            throw new RuntimeException(
                "Ombordagi miqdor 0 yoki undan katta bo'lsin."
            );
        }

        if (mb_strlen($category) > 100) {
            throw new RuntimeException("Kategoriya 100 belgigacha bo'lishi kerak.");
        }

        if (!in_array($isActive, ["0", "1"], true)) {
            throw new RuntimeException("Mahsulot holati noto'g'ri.");
        }

        $price = number_format((float)$priceInput, 2, ".", "");
        $oldPrice = $oldPriceInput === ""
            ? null
            : number_format((float)$oldPriceInput, 2, ".", "");

        /* Yangi rasm tanlanmagan bo'lsa null qaytadi */
        $newImage = uploadProductImage();

        if ($action === "create") {
            $slug = makeSlug($pdo, $name);

            $stmt = $pdo->prepare(
                "INSERT INTO products
                (name, slug, description, category, price, old_price,
                 stock, image, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $name,
                $slug,
                $description !== "" ? $description : null,
                $category !== "" ? $category : null,
                $price,
                $oldPrice,
                $stock,
                $newImage,
                (int)$isActive
            ]);

            goBack("Yangi mahsulot muvaffaqiyatli qo'shildi.");
        }

        /* Mavjud mahsulotni tahrirlash */
        if (!$id || $id < 1) {
            throw new RuntimeException("Mahsulot ID noto'g'ri.");
        }

        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $oldProduct = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$oldProduct) {
            throw new RuntimeException("Mahsulot topilmadi.");
        }

        $slug = makeSlug($pdo, $name, $id);
        $image = $newImage ?? $oldProduct["image"];

        $stmt = $pdo->prepare(
            "UPDATE products
             SET name = ?, slug = ?, description = ?, category = ?,
                 price = ?, old_price = ?, stock = ?, image = ?,
                 is_active = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $name,
            $slug,
            $description !== "" ? $description : null,
            $category !== "" ? $category : null,
            $price,
            $oldPrice,
            $stock,
            $image,
            (int)$isActive,
            $id
        ]);

        if ($newImage !== null) {
            deleteProductImage($oldProduct["image"]);
        }

        goBack("Mahsulot ma'lumotlari yangilandi.");

    } catch (Throwable $e) {
        error_log($e->getMessage());

        if ($e instanceof RuntimeException) {
            goBack($e->getMessage(), "error");
        }

        goBack(
            "Xatolik yuz berdi. Apache/PHP xato jurnalini tekshiring.",
            "error"
        );
    }
}

/* 5. Xabar va tahrirlash ma'lumotlari */
$flash = $_SESSION["product_flash"] ?? null;
unset($_SESSION["product_flash"]);

$editProduct = null;
$editId = filter_var(
    $_GET["edit"] ?? null,
    FILTER_VALIDATE_INT
);

if ($editId && $editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$editId]);
    $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$editProduct) {
        $flash = [
            "message" => "Mahsulot topilmadi.",
            "type" => "error"
        ];
    }
}

/* 6. Mahsulotlar ro'yxati */
$products = $pdo->query(
    "SELECT id, name, category, price, stock, image, is_active, created_at
     FROM products
     ORDER BY id DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mahsulotlarni boshqarish</title>

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    padding: 24px;
    font-family: Arial, sans-serif;
    background: #f3f5f9;
    color: #202938;
}

.container { max-width: 1150px; margin: auto; }

header, .panel {
    background: #fff;
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 22px;
    box-shadow: 0 4px 18px #00000008;
}

h1, h2 { margin-top: 0; }

.grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 7px;
}

input, textarea, select {
    width: 100%;
    padding: 11px;
    border: 1px solid #ccd3df;
    border-radius: 8px;
    font: inherit;
    background: #fff;
}

textarea { min-height: 95px; resize: vertical; }
.field { margin-bottom: 16px; }

.btn {
    display: inline-block;
    border: 0;
    padding: 11px 15px;
    border-radius: 8px;
    background: #2159e8;
    color: white;
    text-decoration: none;
    cursor: pointer;
    font: inherit;
}

.secondary { background: #5c6678; }
.danger { background: #c83232; }
.small { padding: 8px 10px; font-size: 14px; }

.message {
    padding: 13px;
    border-radius: 8px;
    margin-bottom: 20px;
    background: #e3f7e9;
    color: #17652c;
}

.message.error { background: #ffebeb; color: #9e2020; }
.table-wrap { overflow-x: auto; }

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 760px;
}

th, td {
    text-align: left;
    padding: 12px 10px;
    border-bottom: 1px solid #e8ebf1;
}

th { background: #f7f8fb; }

.product-image {
    width: 58px;
    height: 58px;
    object-fit: cover;
    border-radius: 8px;
}

.badge {
    display: inline-block;
    padding: 5px 8px;
    border-radius: 20px;
    background: #e1f6e8;
    color: #17652c;
    font-size: 12px;
}

.inactive { background: #eceff4; color: #4d5666; }
.actions { display: flex; gap: 7px; align-items: center; }
.actions form { margin: 0; }

@media (max-width: 650px) {
    body { padding: 12px; }
    .grid { grid-template-columns: 1fr; }
    header, .panel { padding: 17px; }
}
</style>
</head>

<body>
<div class="container">

<header>
    <h1>Mahsulotlarni boshqarish</h1>
    <p>Mahsulot qo'shing, tahrirlang va o'chiring.</p>
    <a class="btn secondary" href="admin.php">Admin paneli</a>
    <a class="btn secondary" href="index.html">Saytga qaytish</a>
</header>

<?php if ($flash): ?>
    <div class="message <?= h($flash["type"]) ?>">
        <?= h($flash["message"]) ?>
    </div>
<?php endif; ?>

<section class="panel">
    <h2><?= $editProduct ? "Mahsulotni tahrirlash" : "Yangi mahsulot qo'shish" ?></h2>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token"
               value="<?= h($_SESSION["product_csrf"]) ?>">

        <input type="hidden" name="action"
               value="<?= $editProduct ? "update" : "create" ?>">

        <?php if ($editProduct): ?>
            <input type="hidden" name="id"
                   value="<?= (int)$editProduct["id"] ?>">
        <?php endif; ?>

        <div class="grid">
            <div class="field">
                <label for="name">Mahsulot nomi *</label>
                <input id="name" name="name" maxlength="200" required
                       value="<?= h($editProduct["name"] ?? "") ?>">
            </div>

            <div class="field">
                <label for="category">Kategoriya</label>
                <input id="category" name="category" maxlength="100"
                       placeholder="Masalan: Telefon"
                       value="<?= h($editProduct["category"] ?? "") ?>">
            </div>

            <div class="field">
                <label for="price">Narxi (so'm) *</label>
                <input id="price" name="price" type="number"
                       min="0.01" step="0.01" required
                       value="<?= h($editProduct["price"] ?? "") ?>">
            </div>

            <div class="field">
                <label for="old_price">Eski narxi (ixtiyoriy)</label>
                <input id="old_price" name="old_price" type="number"
                       min="0.01" step="0.01"
                       value="<?= h($editProduct["old_price"] ?? "") ?>">
            </div>

            <div class="field">
                <label for="stock">Ombordagi miqdor *</label>
                <input id="stock" name="stock" type="number"
                       min="0" step="1" required
                       value="<?= h($editProduct["stock"] ?? "0") ?>">
            </div>

            <div class="field">
                <label for="is_active">Mahsulot holati</label>
                <select id="is_active" name="is_active">
                    <option value="1"
                        <?= (string)($editProduct["is_active"] ?? "1") === "1" ? "selected" : "" ?>>
                        Saytda faol
                    </option>
                    <option value="0"
                        <?= (string)($editProduct["is_active"] ?? "") === "0" ? "selected" : "" ?>>
                        Yashirilgan
                    </option>
                </select>
            </div>
        </div>

        <div class="field">
            <label for="description">Mahsulot tavsifi</label>
            <textarea id="description" name="description"
                      placeholder="Mahsulot xususiyatlarini yozing"><?= h($editProduct["description"] ?? "") ?></textarea>
        </div>

        <div class="field">
            <label for="image">Mahsulot rasmi (3 MB gacha)</label>
            <input id="image" name="image" type="file"
                   accept="image/jpeg,image/png,image/webp,image/gif">

            <?php if (!empty($editProduct["image"])): ?>
                <p>Hozirgi rasm:</p>
                <img class="product-image"
                     src="<?= h($editProduct["image"]) ?>"
                     alt="Mahsulot rasmi">
                <p>Yangi rasm tanlamasangiz, eski rasm saqlanadi.</p>
            <?php endif; ?>
        </div>

        <button class="btn" type="submit">
            <?= $editProduct ? "O'zgarishlarni saqlash" : "Mahsulot qo'shish" ?>
        </button>

        <?php if ($editProduct): ?>
            <a class="btn secondary" href="admin_products.php">Bekor qilish</a>
        <?php endif; ?>
    </form>
</section>

<section class="panel">
    <h2>Mahsulotlar ro'yxati (<?= count($products) ?>)</h2>

    <?php if (!$products): ?>
        <p>Hozircha mahsulot yo'q. Yuqoridagi forma orqali mahsulot qo'shing.</p>
    <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Rasm</th>
                    <th>Mahsulot</th>
                    <th>Narxi</th>
                    <th>Miqdor</th>
                    <th>Holati</th>
                    <th>Amallar</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= (int)$product["id"] ?></td>

                    <td>
                        <?php if (!empty($product["image"])): ?>
                            <img class="product-image"
                                 src="<?= h($product["image"]) ?>"
                                 alt="<?= h($product["name"]) ?>">
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>

                    <td>
                        <strong><?= h($product["name"]) ?></strong><br>
                        <small><?= h($product["category"] ?? "") ?></small>
                    </td>

                    <td>
                        <?= number_format((float)$product["price"], 0, ".", " ") ?> so'm
                    </td>

                    <td><?= (int)$product["stock"] ?></td>

                    <td>
                        <span class="badge <?= (int)$product["is_active"] === 1 ? "" : "inactive" ?>">
                            <?= (int)$product["is_active"] === 1 ? "Faol" : "Yashirilgan" ?>
                        </span>
                    </td>

                    <td>
                        <div class="actions">
                            <a class="btn small"
                               href="admin_products.php?edit=<?= (int)$product["id"] ?>">
                                Tahrirlash
                            </a>

                            <form method="POST"
                                  onsubmit="return confirm('Mahsulotni o‘chirishni tasdiqlaysizmi?')">
                                <input type="hidden" name="csrf_token"
                                       value="<?= h($_SESSION["product_csrf"]) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id"
                                       value="<?= (int)$product["id"] ?>">

                                <button class="btn danger small" type="submit">
                                    O'chirish
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>

</div>
</body>
</html>