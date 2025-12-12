<?php
function send_json_response($data, $statusCode = 200) {
    header_remove();
    header("Content-Type: application/json");
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}
?>