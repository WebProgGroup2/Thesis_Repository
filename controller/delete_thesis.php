<?php
require_once __DIR__ . '/../config/connection.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $thesis_id = intval($input['thesis_id'] ?? 0);

    if (!$thesis_id) {
        http_response_code(400); 
        echo json_encode(["success" => false, "error" => "Invalid thesis ID"]);
        exit;
    }

    $tables = ['thesis_authors', 'thesis_keyword', 'thesis_refer', 'thesis_types'];
    foreach ($tables as $table) {
        $stmt = $conn->prepare("DELETE FROM $table WHERE thesis_id = ?");
        $stmt->execute([$thesis_id]);
    }

    $stmt = $conn->prepare("DELETE FROM thesis WHERE thesis_id = ?");
    $stmt->execute([$thesis_id]);


    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Thesis deleted successfully!"]);
    } else {
        http_response_code(404); 
        echo json_encode(["success" => false, "error" => "Thesis not found or already deleted"]);
    }
} catch (PDOException $e) {
    http_response_code(500); 
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>