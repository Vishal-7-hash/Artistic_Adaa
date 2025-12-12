<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers.php';

$database = new Database(); $db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['userId'] ?? null; $itemId = $data['itemId'] ?? null;
if (!$userId || !$itemId) { send_json_response(['error' => 'User ID and Item ID are required.'], 400); }

$query = "SELECT id, quantity FROM cart WHERE user_id = :user_id AND item_id = :item_id";
$stmt = $db->prepare($query); $stmt->bindParam(':user_id', $userId); $stmt->bindParam(':item_id', $itemId);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
    $newQuantity = $cartItem['quantity'] + 1;
    $updateQuery = "UPDATE cart SET quantity = :quantity WHERE id = :id";
    $updateStmt = $db->prepare($updateQuery); $updateStmt->bindParam(':quantity', $newQuantity); $updateStmt->bindParam(':id', $cartItem['id']);
    $updateStmt->execute();
} else {
    $insertQuery = "INSERT INTO cart (user_id, item_id, quantity) VALUES (:user_id, :item_id, 1)";
    $insertStmt = $db->prepare($insertQuery); $insertStmt->bindParam(':user_id', $userId); $insertStmt->bindParam(':item_id', $itemId);
    $insertStmt->execute();
}
send_json_response(['message' => 'Item added to cart successfully.']);
?>