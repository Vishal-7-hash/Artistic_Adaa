<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers.php';

$database = new Database(); $db = $database->getConnection();
$userId = $_GET['userId'] ?? null;
if (!$userId) { send_json_response(['error' => 'User ID is required.'], 400); }

$query = "SELECT c.id as cartId, c.quantity, p.id as itemId, p.itemName, p.price, p.image_path FROM cart c JOIN portfolio_items p ON c.item_id = p.id WHERE c.user_id = :user_id";
$stmt = $db->prepare($query); $stmt->bindParam(':user_id', $userId);
$stmt->execute();
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($cartItems as &$item) {
    $item['image_url'] = 'uploads/' . $item['image_path'];
}

send_json_response($cartItems);
?>