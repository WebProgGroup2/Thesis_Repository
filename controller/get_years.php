<?php
require_once __DIR__ . '/../config/connection.php';

try {
    $stmt = $conn->query("
        SELECT DISTINCT YEAR(pub_date) AS year
        FROM Thesis
        WHERE pub_date IS NOT NULL
        ORDER BY year DESC
    ");
    $years = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        "success" => true,
        "years" => $years
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
