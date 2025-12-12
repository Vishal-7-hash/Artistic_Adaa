<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { send_json_response(['error' => 'Method not allowed'], 405); }

// --- Profile Picture Upload Logic ---
$profilePicFilename = null;
if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../../uploads/profile_pictures/';
    $imageFileType = strtolower(pathinfo($_FILES["profilePicture"]["name"], PATHINFO_EXTENSION));
    $uniqueFilename = uniqid('user_', true) . '.' . $imageFileType;
    $targetFile = $uploadDir . $uniqueFilename;
    
    $check = getimagesize($_FILES["profilePicture"]["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $targetFile)) {
            $profilePicFilename = $uniqueFilename;
        }
    }
}

// --- Database Insertion Logic ---
$database = new Database(); $db = $database->getConnection();
$name = $_POST['name'] ?? null; $email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null; $userType = $_POST['userType'] ?? null;

if (!$name || !$email || !$password || !$userType) { send_json_response(['error' => 'All text fields are required.'], 400); }

$query = "SELECT id FROM users WHERE email = :email LIMIT 1";
$stmt = $db->prepare($query); $stmt->bindParam(':email', $email); $stmt->execute();
if ($stmt->rowCount() > 0) { send_json_response(['error' => 'An account with this email already exists.'], 409); }

// Generate a unique username
$baseUsername = strtolower(str_replace(' ', '', $name)); $username = $baseUsername; $counter = 1;
while (true) { $query = "SELECT id FROM users WHERE username = :username LIMIT 1"; $stmt = $db->prepare($query); $stmt->bindParam(':username', $username); $stmt->execute(); if ($stmt->rowCount() == 0) { break; } $username = $baseUsername . $counter; $counter++; }

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$query = "INSERT INTO users (name, username, email, password, user_type, profile_picture_path) VALUES (:name, :username, :email, :password, :user_type, :pfp)";
$stmt = $db->prepare($query);
$stmt->bindParam(':name', $name); $stmt->bindParam(':username', $username); $stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $hashedPassword); $stmt->bindParam(':user_type', $userType);
$stmt->bindParam(':pfp', $profilePicFilename);

if ($stmt->execute()) { send_json_response(['message' => 'User registered successfully. Your unique username is: ' . $username], 201);
} else { send_json_response(['error' => 'Registration failed.'], 500); }
?>