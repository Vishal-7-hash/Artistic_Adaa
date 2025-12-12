<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers.php';

$database = new Database();
$db = $database->getConnection();
$query = "SELECT pi.id, pi.artisan_id, pi.itemName, pi.description, pi.price, pi.image_path, u.name as artisanName FROM portfolio_items pi JOIN users u ON pi.artisan_id = u.id ORDER BY pi.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($items as &$item) {
    $item['image_url'] = 'uploads/' . $item['image_path'];
}

send_json_response($items);
?>