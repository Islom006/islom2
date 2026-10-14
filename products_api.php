<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'db.php';

try {
    $sql = "SELECT id, name, category, description,
                   price, old_price, stock, image
            FROM products
            WHERE is_active = 1
            ORDER BY created_at DESC";

    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'products' => $products
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Mahsulotlarni yuklashda xatolik yuz berdi.'
    ], JSON_UNESCAPED_UNICODE);
}