<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { send_json_response(['error' => 'Method not allowed'], 405); }

if (!isset($_FILES['craftImage']) || $_FILES['craftImage']['error'] !== UPLOAD_ERR_OK) { send_json_response(['error' => 'Image upload failed or no image was sent.'], 400); }
$uploadDir = __DIR__ . '/../../uploads/';
$imageFileType = strtolower(pathinfo($_FILES["craftImage"]["name"], PATHINFO_EXTENSION));
$uniqueFilename = uniqid('craft_', true) . '.' . $imageFileType;
$targetFile = $uploadDir . $uniqueFilename;
$check = getimagesize($_FILES["craftImage"]["tmp_name"]);
if($check === false) { send_json_response(['error' => 'File is not an image.'], 400); }
if ($_FILES["craftImage"]["size"] > 5000000) { send_json_response(['error' => 'Sorry, your file is too large.'], 400); }
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) { send_json_response(['error' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.'], 400); }
if (!move_uploaded_file($_FILES["craftImage"]["tmp_name"], $targetFile)) { send_json_response(['error' => 'Sorry, there was an error uploading your file.'], 500); }

$database = new Database();
$db = $database->getConnection();

$artisanId = $_POST['artisanId'] ?? null;
$itemName = $_POST['itemName'] ?? null;
$description = $_POST['description'] ?? null;
$price = $_POST['price'] ?? null;

if (!$artisanId || !$itemName || !$description || !$price) {
    unlink($targetFile);
    send_json_response(['error' => 'All fields, including price, are required.'], 400);
}

$query = "INSERT INTO portfolio_items (artisan_id, itemName, description, price, image_path) VALUES (:artisan_id, :itemName, :description, :price, :image_path)";
$stmt = $db->prepare($query);
$stmt->bindParam(':artisan_id', $artisanId);
$stmt->bindParam(':itemName', $itemName);
$stmt->bindParam(':description', $description);
$stmt->bindParam(':price', $price);
$stmt->bindParam(':image_path', $uniqueFilename);

if ($stmt->execute()) {
    send_json_response(['message' => 'Portfolio item with image created successfully.'], 201);
} else {
    unlink($targetFile);
    send_json_response(['error' => 'Failed to create item in database.'], 500);
}
?>