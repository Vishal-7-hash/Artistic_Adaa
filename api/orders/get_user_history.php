<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers.php';

$database = new Database(); $db = $database->getConnection();
$userId = $_GET['userId'] ?? null;
if (!$userId) { send_json_response(['error' => 'User ID is required.'], 400); }

$query = "SELECT id, total_amount, order_date FROM orders WHERE user_id = :user_id ORDER BY order_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$itemQuery = "SELECT oi.quantity, oi.price, pi.itemName, pi.image_path FROM order_items oi JOIN portfolio_items pi ON oi.item_id = pi.id WHERE oi.order_id = :order_id";
$itemStmt = $db->prepare($itemQuery);

foreach ($orders as &$order) {
    $itemStmt->bindParam(':order_id', $order['id']);
    $itemStmt->execute();
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as &$item) {
        $item['image_url'] = 'uploads/' . $item['image_path'];
    }
    $order['items'] = $items;
}

send_json_response($orders);
?>