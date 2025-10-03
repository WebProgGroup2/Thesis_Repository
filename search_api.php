<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json; charset=utf-8");

$host = "127.0.0.1";
$user = "root";
$pass = "";
$dbname = "nu_thesis";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$q = isset($_GET['q']) ? trim($_GET['q']) : "";

// SQL with JOIN to get author names
if ($q === "") {
    $sql = "
        SELECT t.thesis_id AS id,
               t.title,
               GROUP_CONCAT(CONCAT(a.firstname, ' ', a.middlename, ' ', a.lastname) SEPARATOR ', ') AS authors,
               t.abstract
        FROM thesis t
        LEFT JOIN thesis_authors ta ON t.thesis_id = ta.thesis_id
        LEFT JOIN authors a ON ta.author_id = a.author_id
        GROUP BY t.thesis_id
        ORDER BY t.thesis_id DESC
    ";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "
        SELECT t.thesis_id AS id,
               t.title,
               GROUP_CONCAT(CONCAT(a.firstname, ' ', a.middlename, ' ', a.lastname) SEPARATOR ', ') AS authors,
               t.abstract
        FROM thesis t
        LEFT JOIN thesis_authors ta ON t.thesis_id = ta.thesis_id
        LEFT JOIN authors a ON ta.author_id = a.author_id
        WHERE t.title LIKE ? OR
              CONCAT(a.firstname, ' ', a.middlename, ' ', a.lastname) LIKE ? OR
              t.abstract LIKE ?
        GROUP BY t.thesis_id
        ORDER BY t.thesis_id DESC
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $term = "%{$q}%";
        $stmt->bind_param("sss", $term, $term, $term);
    }
}

if (!$stmt || !$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "Query execution failed"]);
    exit;
}

$res = $stmt->get_result();
$out = [];

while ($row = $res->fetch_assoc()) {
    // Limit abstract to 20 words
    $words = explode(' ', $row['abstract']);
    if (count($words) > 10) {
        $row['abstract'] = implode(' ', array_slice($words, 0, 20)) . '...';
    }
    $out[] = $row;
}

echo json_encode($out);
$stmt->close();
$conn->close();
