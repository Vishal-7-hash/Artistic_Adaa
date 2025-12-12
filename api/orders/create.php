<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers.php';

$database = new Database(); $db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['userId'] ?? null;
if (!$userId) { send_json_response(['error' => 'User ID is required.'], 400); }

try {
    $db->beginTransaction();
    
    // Get cart items
    $cartQuery = "SELECT c.quantity, p.id as itemId, p.price, p.artisan_id FROM cart c JOIN portfolio_items p ON c.item_id = p.id WHERE c.user_id = :user_id";
    $cartStmt = $db->prepare($cartQuery); $cartStmt->bindParam(':user_id', $userId);
    $cartStmt->execute();
    $cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($cartItems) === 0) { send_json_response(['error' => 'Cart is empty.'], 400); }
    
    // Calculate total amount
    $totalAmount = 0;
    foreach ($cartItems as $item) { $totalAmount += $item['price'] * $item['quantity']; }
    
    // Create order
    $orderQuery = "INSERT INTO orders (user_id, total_amount) VALUES (:user_id, :total_amount)";
    $orderStmt = $db->prepare($orderQuery); $orderStmt->bindParam(':user_id', $userId); $orderStmt->bindParam(':total_amount', $totalAmount);
    $orderStmt->execute();
    $orderId = $db->lastInsertId();
    
    // Create order items
    $orderItemsQuery = "INSERT INTO order_items (order_id, item_id, artisan_id, quantity, price) VALUES (:order_id, :item_id, :artisan_id, :quantity, :price)";
    $orderItemsStmt = $db->prepare($orderItemsQuery);
    foreach ($cartItems as $item) {
        $orderItemsStmt->bindParam(':order_id', $orderId);
        $orderItemsStmt->bindParam(':item_id', $item['itemId']);
        $orderItemsStmt->bindParam(':artisan_id', $item['artisan_id']);
        $orderItemsStmt->bindParam(':quantity', $item['quantity']);
        $orderItemsStmt->bindParam(':price', $item['price']);
        $orderItemsStmt->execute();
    }
    
    // Clear cart
    $clearCartQuery = "DELETE FROM cart WHERE user_id = :user_id";
    $clearCartStmt = $db->prepare($clearCartQuery); $clearCartStmt->bindParam(':user_id', $userId);
    $clearCartStmt->execute();
    
    $db->commit();
    send_json_response(['message' => 'Order placed successfully!']);

} catch (Exception $e) {
    $db->rollBack();
    send_json_response(['error' => 'Failed to place order: ' . $e->getMessage()], 500);
}
?>