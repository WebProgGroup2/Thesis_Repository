<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/connection.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing thesis ID']);
    exit;
}

$thesis_id = intval($_GET['id']);

try {
    $stmt = $conn->prepare("
        SELECT 
            t.thesis_id,
            t.title,
            t.thesis_cover,
            t.abstract,
            m.method_name AS methodology,
            m.method_id,

            -- Combine all unique thesis_types
            GROUP_CONCAT(DISTINCT tt.type_id ORDER BY tt.type_id SEPARATOR ', ') AS thesis_types_id,

            -- Combine all unique thesis_types_name
            GROUP_CONCAT(DISTINCT tt.type_name ORDER BY tt.type_id SEPARATOR ', ') AS thesis_types_name,

            t.pub_date,
            t.pub_place,
            t.file_location,
            t.page_count,
            t.program_id,

            -- Combine all unique keywords
            GROUP_CONCAT(DISTINCT k.keyword ORDER BY k.keyword SEPARATOR ', ') AS keywords,

            -- Combine all unique authors
                (
                SELECT CONCAT(
                    '[', 
                    GROUP_CONCAT(
                        DISTINCT CONCAT(
                            '{',
                            '\"firstname\": \"', a2.firstname, '\", ',
                            '\"middlename\": \"', COALESCE(a2.middlename, ''), '\", ',
                            '\"lastname\": \"', a2.lastname, '\"',
                            '}'
                        )
                        ORDER BY a2.author_id SEPARATOR ','
                    ),
                    ']'
                )
                FROM authors a2
                JOIN thesis_authors ta2 ON ta2.author_id = a2.author_id
                WHERE ta2.thesis_id = t.thesis_id
                ) AS authors,



            -- Combine all unique references
            GROUP_CONCAT(DISTINCT rl.reference ORDER BY rl.ref_id SEPARATOR '|||') AS references_list

        FROM thesis t
        JOIN methodology m ON m.method_id = t.method_id
        LEFT JOIN thesis_keyword tk ON tk.thesis_id = t.thesis_id
        LEFT JOIN keywords k ON tk.keyword_id = k.keyword_id
        LEFT JOIN thesis_authors ta ON ta.thesis_id = t.thesis_id
        LEFT JOIN authors a ON a.author_id = ta.author_id
        LEFT JOIN thesis_refer tr ON tr.thesis_id = t.thesis_id
        LEFT JOIN references_list rl ON rl.ref_id = tr.ref_id
        LEFT JOIN thesis_types tts on tts.thesis_id = t.thesis_id
        LEFT JOIN thesis_type tt on tts.type_id = tt.type_id

        WHERE t.thesis_id = :thesis_id
        GROUP BY 
            t.thesis_id, t.title, t.abstract, m.method_name,
            t.pub_date, t.pub_place, t.file_location
    ");

    $stmt->execute(['thesis_id' => $thesis_id]);
    $thesis = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$thesis) {
        echo json_encode(['error' => 'Thesis not found']);
        exit;
    }

    $thesis['keywords'] = !empty($thesis['keywords'])
        ? array_map('trim', explode(',', $thesis['keywords']))
        : [];

    $thesis['authors'] = !empty($thesis['authors'])
        ? json_decode($thesis['authors'], true)
        : [];

    $thesis['references_list'] = !empty($thesis['references_list'])
        ? array_map('trim', explode('|||', $thesis['references_list']))
        : [];

    echo json_encode($thesis, JSON_PRETTY_PRINT);


} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>