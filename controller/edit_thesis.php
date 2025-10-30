<?php
require_once __DIR__ . '/../config/connection.php';

header('Content-Type: application/json');

try {
    // Read JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input || empty($input['thesis_id'])) {
        echo json_encode(["success" => false, "error" => "Missing thesis ID or invalid request."]);
        exit;
    }

    $thesis_id = intval($input['thesis_id'] ?? 0);
    $title = trim($input['title'] ?? '');
    $pub_place = trim($input['pub_place'] ?? '');
    $pub_date = $input['pub_date'] ?? null;
    $abstract = $input['abstract'] ?? '';
    $page_count = intval($input['page_count'] ?? 0);
    $methodology_id = intval($input['methodology_id'] ?? 0);
    $program_id = intval($input['program_id'] ?? 0);
    $cover_path = trim($input['cover_path'] ?? '');
    $pdf_path = trim($input['pdf_path'] ?? '');
    $authors = $input['authors'] ?? [];
    $thesis_types = $input['thesis_types'] ?? [];
    $keywords = $input['keywords'] ?? [];
    $references = $input['references'] ?? [];

    $errors = [];

    // Validate required fields with proper empty checks
    if (empty($title) || strlen(trim($title)) === 0) {
        $errors[] = "Title is required and cannot be empty";
    }

    if (empty($pub_date)) {
        $errors[] = "Publication date is required";
    } elseif (!strtotime($pub_date)) {
        $errors[] = "Invalid publication date format";
    }

    if ($methodology_id <= 0) {
        $errors[] = "Valid methodology is required";
    }

    if ($program_id <= 0) {
        $errors[] = "Valid program is required";
    }

    // Validate field lengths
    if (strlen($title) > 255) { // Adjust based on your DB column length
        $errors[] = "Title must be less than 255 characters";
    }

    if (strlen($abstract) > 2000) { // Adjust based on your DB
        $errors[] = "Abstract must be less than 2000 characters";
    }

    // Validate numeric ranges
    if ($page_count < 0) {
        $errors[] = "Page count cannot be negative";
    }

    if ($page_count > 10000) { // Reasonable upper limit
        $errors[] = "Page count is unrealistically high";
    }

    // Validate arrays (if required)
    if (empty($authors)) {
        $errors[] = "At least one author is required";
    }

    if (empty($thesis_types)) {
        $errors[] = "At least one thesis type is required";
    }

    // Remove the FILTER_VALIDATE_URL checks and replace with:

    if (!empty($cover_path)) {
        // Check for path traversal attacks
        if (str_contains($cover_path, '..') || str_contains($cover_path, '//')) {
            $errors[] = "Invalid cover file path";
        }

        // Check file extension
        $allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($cover_path, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed_image_extensions)) {
            $errors[] = "Cover image must be JPG, PNG, GIF, or WebP";
        }

        // Ensure path starts with /uploads/
        if (!str_starts_with($cover_path, 'uploads/')) {
            $errors[] = "Cover file must be in uploads directory";
        }
    }

    if (!empty($pdf_path)) {
        // Check for path traversal attacks
        if (str_contains($pdf_path, '..') || str_contains($pdf_path, '//')) {
            $errors[] = "Invalid PDF file path";
        }

        // Check file extension
        if (!str_ends_with(strtolower($pdf_path), '.pdf')) {
            $errors[] = "PDF file must have .pdf extension";
        }

        // Ensure path starts with /uploads/
        if (!str_starts_with($pdf_path, 'uploads/')) {
            $errors[] = "PDF file must be in uploads directory";
        }
    }

    // Check if foreign keys exist in db
    if ($methodology_id > 0) {
        $stmt = $conn->prepare("SELECT method_id FROM methodology WHERE method_id = ?");
        $stmt->execute([$methodology_id]);
        if (!$stmt->fetch()) {
            $errors[] = "Selected methodology does not exist";
        }
    }

    if ($program_id > 0) {
        $stmt = $conn->prepare("SELECT program_id FROM programs WHERE program_id = ?");
        $stmt->execute([$program_id]);
        if (!$stmt->fetch()) {
            $errors[] = "Selected program does not exist";
        }
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Validation failed",
            "details" => $errors
        ]);
        exit;
    }

   
    $conn->beginTransaction();

    $updateFields = [
        "title = ?",
        "pub_place = ?",
        "pub_date = ?",
        "abstract = ?",
        "page_count = ?",
        "method_id = ?",
        "program_id = ?"
    ];

    $updateValues = [
        $title,
        $pub_place,
        $pub_date,
        $abstract,
        $page_count,
        $methodology_id,
        $program_id
    ];

    // check empty path of image anf file
    if (!empty($cover_path)) {
        $updateFields[] = "thesis_cover = ?";
        $updateValues[] = $cover_path;
    }

    if (!empty($pdf_path)) {
        $updateFields[] = "file_location = ?";
        $updateValues[] = $pdf_path;
    }

    $updateValues[] = $thesis_id; 

    $sql = "UPDATE thesis SET " . implode(", ", $updateFields) . " WHERE thesis_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($updateValues);

    // ============================================================
    // ============================================================
    $conn->prepare("DELETE FROM thesis_authors WHERE thesis_id = ?")->execute([$thesis_id]);

    $authorStmt = $conn->prepare("INSERT INTO thesis_authors (thesis_id, author_id) VALUES (?, ?)");

    foreach ($authors as $authorData) {
        $firstname = trim($authorData['firstname'] ?? '');
        $middlename = trim($authorData['middlename'] ?? '');
        $lastname = trim($authorData['lastname'] ?? '');

        if (empty($firstname) || empty($lastname)) {
            continue; // Skip invalid author input
        }

        // both find or insert author
        $findAuthor = $conn->prepare("SELECT author_id FROM authors WHERE firstname = ? AND lastname = ? AND (middlename = ? OR (middlename IS NULL AND ? = ''))");
        $findAuthor->execute([$firstname, $lastname, $middlename, $middlename]);
        $author = $findAuthor->fetch(PDO::FETCH_ASSOC);

        if ($author) {
            $author_id = $author['author_id'];
        } else {
            $insertAuthor = $conn->prepare("INSERT INTO authors (firstname, middlename, lastname) VALUES (?, ?, ?)");
            $insertAuthor->execute([$firstname, $middlename ?: null, $lastname]);
            $author_id = $conn->lastInsertId();
        }

        $authorStmt->execute([$thesis_id, $author_id]);
    }

    // ============================================================
    // ============================================================
    $conn->prepare("DELETE FROM thesis_types WHERE thesis_id = ?")->execute([$thesis_id]);

    if (!empty($thesis_types)) {
        $typeStmt = $conn->prepare("INSERT INTO thesis_types (thesis_id, type_id) VALUES (?, ?)");
        foreach ($thesis_types as $type_id) {
            $type_id = intval($type_id);
            if ($type_id > 0) {
                // Verify type_id exists
                $checkType = $conn->prepare("SELECT type_id FROM thesis_type WHERE type_id = ?");
                $checkType->execute([$type_id]);
                if ($checkType->fetch()) {
                    $typeStmt->execute([$thesis_id, $type_id]);
                }
            }
        }
    }

    // ============================================================
    // ============================================================
    $conn->prepare("DELETE FROM thesis_keyword WHERE thesis_id = ?")->execute([$thesis_id]);

    if (!empty($keywords)) {
        $keywordStmt = $conn->prepare("INSERT INTO thesis_keyword (thesis_id, keyword_id) VALUES (?, ?)");
        $findKeyword = $conn->prepare("SELECT keyword_id FROM keywords WHERE keyword = ?");
        $insertKeyword = $conn->prepare("INSERT INTO keywords (keyword) VALUES (?)");

        foreach ($keywords as $kw) {
            $kw = trim($kw);
            if (empty($kw))
                continue;

            $findKeyword->execute([$kw]);
            $row = $findKeyword->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $keyword_id = $row['keyword_id'];
            } else {
                $insertKeyword->execute([$kw]);
                $keyword_id = $conn->lastInsertId();
            }
            $keywordStmt->execute([$thesis_id, $keyword_id]);
        }
    }

    // ============================================================
    // ============================================================
    $conn->prepare("DELETE FROM thesis_refer WHERE thesis_id = ?")->execute([$thesis_id]);

    if (!empty($references)) {
        $findRef = $conn->prepare("SELECT ref_id FROM references_list WHERE reference = ?");
        $insertRef = $conn->prepare("INSERT INTO references_list (reference) VALUES (?)");
        $linkRef = $conn->prepare("INSERT INTO thesis_refer (thesis_id, ref_id) VALUES (?, ?)");

        foreach ($references as $ref) {
            $ref = trim($ref);
            if (empty($ref))
                continue;

            $findRef->execute([$ref]);
            $refRow = $findRef->fetch(PDO::FETCH_ASSOC);

            if ($refRow) {
                $ref_id = $refRow['ref_id'];
            } else {

                $insertRef->execute([$ref]);
                $ref_id = $conn->lastInsertId();
            }

            $linkRef->execute([$thesis_id, $ref_id]);
        }
    }

    $conn->commit();

    echo json_encode(["success" => true, "message" => "Thesis updated successfully."]);

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(["success" => false, "error" => "An unexpected error occurred: " . $e->getMessage()]);
}
?>