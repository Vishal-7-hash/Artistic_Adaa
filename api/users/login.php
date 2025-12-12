<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { send_json_response(['error' => 'Method not allowed'], 405); }
$database = new Database(); $db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), true);
$login = $data['login'] ?? null; $password = $data['password'] ?? null;

if (!$login || !$password) { send_json_response(['error' => 'Login credential and password are required.'], 400); }

$query = "SELECT id, name, username, email, password, user_type, profile_picture_path FROM users WHERE email = :login OR username = :login LIMIT 1";
$stmt = $db->prepare($query); $stmt->bindParam(':login', $login);
$stmt->execute();

if ($stmt->rowCount() == 1) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (password_verify($password, $user['password'])) {
        // FIX: Correctly create the relative URL for the browser
        $pfp_url = $user['profile_picture_path'] ? 'uploads/profile_pictures/' . $user['profile_picture_path'] : null;
        send_json_response([
            'userId' => $user['id'], 'name' => $user['name'], 'username' => $user['username'],
            'userType' => $user['user_type'], 'profilePictureUrl' => $pfp_url
        ]);
    } else { send_json_response(['error' => 'Invalid credentials.'], 401); }
} else { send_json_response(['error' => 'Invalid credentials.'], 401); }
?>