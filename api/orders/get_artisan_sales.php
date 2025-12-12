<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers.php';

$database = new Database(); $db = $database->getConnection();
$artisanId = $_GET['artisanId'] ?? null;
if (!$artisanId) { send_json_response(['error' => 'Artisan ID is required.'], 400); }

$query = "SELECT oi.quantity, oi.price, pi.itemName, o.order_date, u.name as customerName 
          FROM order_items oi 
          JOIN orders o ON oi.order_id = o.id
          JOIN users u ON o.user_id = u.id
          JOIN portfolio_items pi ON oi.item_id = pi.id
          WHERE oi.artisan_id = :artisan_id 
          ORDER BY o.order_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':artisan_id', $artisanId);
$stmt->execute();
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

send_json_response($sales);
?>