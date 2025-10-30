<?php
require_once __DIR__ . '/../config/connection.php';

header("Content-Type: application/json");

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("No JSON data received.");
    }

    $title = trim($data['title'] ?? '');
    $method_id = intval($data['methodology_id'] ?? 0);
    $program_id = intval($data['program_id'] ?? 0);
    $pub_date = $data['pub_date'] ?? null;
    $pub_place = trim($data['pub_place'] ?? '');
    $page_count = intval($data['page_count'] ?? 0);
    $abstract = $data['abstract'] ?? '';
    $cover_path = $data['cover_path'] ?? '';
    $pdf_path = $data['pdf_path'] ?? '';
    $authors = $data['authors'] ?? [];
    $thesis_types = $data['thesis_types'] ?? [];
    $keywords = $data['keywords'] ?? [];
    $references = $data['references'] ?? [];

    if (!$title || !$method_id || !$program_id || !$cover_path || !$pdf_path) {
        throw new Exception("Missing required fields.");
    }

    $conn->beginTransaction();

    // ----------------------------------------------------to
    // ----------------------------------------------------thesis
    $stmt = $conn->prepare("
        INSERT INTO thesis 
        (title, thesis_cover, method_id, program_id, pub_date, pub_place, page_count, abstract, file_location, visit_count)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
    ");
    $stmt->execute([
        $title,
        $cover_path,
        $method_id,
        $program_id,
        $pub_date,
        $pub_place,
        $page_count,
        $abstract,
        $pdf_path
    ]);

    $thesis_id = $conn->lastInsertId();

    // ----------------------------------------------------to
    // ----------------------------------------------------authors
    $authorInsert = $conn->prepare("INSERT INTO authors (firstname, middlename, lastname) VALUES (?, ?, ?)");
    $linkAuthor = $conn->prepare("INSERT INTO thesis_authors (thesis_id, author_id) VALUES (?, ?)");

    foreach ($authors as $author) {
        $firstname = trim($author['firstname']);
        $lastname = trim($author['lastname']);
        $middlename = trim($author['middlename']);

        $check = $conn->prepare("SELECT author_id FROM authors WHERE firstname=? AND lastname=? AND middlename=? LIMIT 1");
        $check->execute([$firstname, $lastname, $middlename]);
        $author_id = $check->fetchColumn();

        if (!$author_id) {
            $authorInsert->execute([$firstname, $middlename, $lastname]);
            $author_id = $conn->lastInsertId();
        }

        $linkAuthor->execute([$thesis_id, $author_id]);
    }

    // ----------------------------------------------------for
    // ----------------------------------------------------thesis types
    $linkType = $conn->prepare("INSERT INTO thesis_types (thesis_id, type_id) VALUES (?, ?)");
    foreach ($thesis_types as $type_id) {
        $linkType->execute([$thesis_id, $type_id]);
    }

    // ----------------------------------------------------keywords
    // ----------------------------------------------------
    $insertKeyword = $conn->prepare("INSERT INTO keywords (keyword) VALUES (?)");
    $linkKeyword = $conn->prepare("INSERT INTO thesis_keyword (thesis_id, keyword_id) VALUES (?, ?)");

    foreach ($keywords as $keyword) {
        $keyword = trim($keyword);
        if ($keyword === '')
            continue;

        $check = $conn->prepare("SELECT keyword_id FROM keywords WHERE keyword=? LIMIT 1");
        $check->execute([$keyword]);
        $keyword_id = $check->fetchColumn();

        if (!$keyword_id) {
            $insertKeyword->execute([$keyword]);
            $keyword_id = $conn->lastInsertId();
        }

        $linkKeyword->execute([$thesis_id, $keyword_id]);
    }

    // ----------------------------------------------------references
    // ----------------------------------------------------
    $insertRef = $conn->prepare("INSERT INTO references_list (reference) VALUES (?)");
    $linkRef = $conn->prepare("INSERT INTO thesis_refer (thesis_id, ref_id) VALUES (?, ?)");

    foreach ($references as $ref) {
        $ref = trim($ref);
        if ($ref === '')
            continue;

        $insertRef->execute([$ref]);
        $ref_id = $conn->lastInsertId();
        $linkRef->execute([$thesis_id, $ref_id]);
    }

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Thesis saved successfully.",
        "thesis_id" => $thesis_id
    ]);

} catch (Exception $e) {
    // Rollback DB
    if ($conn->inTransaction())
        $conn->rollBack();

    // ----------------------------------------------------
    // ----------------------------------------------------
    $uploadsDir = realpath(__DIR__ . '/../uploads');
    $paths = [$data['cover_path'] ?? '', $data['pdf_path'] ?? ''];

    foreach ($paths as $relativePath) {
        if (!$relativePath)
            continue;

        $fullPath = realpath(__DIR__ . '/../' . $relativePath);
        if ($fullPath && strpos($fullPath, $uploadsDir) === 0 && file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>