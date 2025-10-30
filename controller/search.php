<?php
require_once __DIR__ . '/../config/connection.php';

if (isset($_GET['ajax'])) {
    header("Content-Type: application/json; charset=UTF-8");

    $q = trim($_GET['q'] ?? '');
    $filter = $_GET['filter'] ?? 'all';
    $programs = array_filter(explode(',', $_GET['programs'] ?? ''));
    $methods = array_filter(explode(',', $_GET['methods'] ?? ''));
    $types = array_filter(explode(',', $_GET['types'] ?? ''));

    try {
        if ($q === '') {
            $sql = "
            SELECT 
                t.thesis_id, 
                t.title,
                t.pub_place,
                t.pub_date,
                t.abstract,
                m.method_name AS methodology,
                t.visit_count,

                -- Get only the first author
                (
                    SELECT CONCAT(a2.firstname, ' ', a2.middlename, ' ', a2.lastname)
                    FROM Thesis_Authors ta2
                    JOIN Authors a2 ON ta2.author_id = a2.author_id
                    WHERE ta2.thesis_id = t.thesis_id
                    ORDER BY ta2.author_id ASC
                    LIMIT 1
                ) AS author,

                GROUP_CONCAT(DISTINCT tt.type_name SEPARATOR ', ') AS types,
                GROUP_CONCAT(DISTINCT p.acronym SEPARATOR ', ') AS programs
            FROM Thesis t
            JOIN Methodology m ON t.method_id = m.method_id
            LEFT JOIN Programs p ON t.program_id = p.program_id
            LEFT JOIN Thesis_Authors ta ON t.thesis_id = ta.thesis_id
            LEFT JOIN Authors a ON ta.author_id = a.author_id
            JOIN thesis_types tts on tts.thesis_id = t.thesis_id
            JOIN thesis_type tt on tt.type_id = tts.type_id
            WHERE 1
        ";

            $params = [];
            if (!empty($programs)) {
                $placeholders = implode(',', array_fill(0, count($programs), '?'));
                $sql .= " AND p.acronym IN ($placeholders)";
                $params = array_merge($params, $programs);
            }

            if (!empty($methods)) {
                $placeholders = implode(',', array_fill(0, count($methods), '?'));
                $sql .= " AND m.method_name IN ($placeholders)";
                $params = array_merge($params, $methods);
            }

            if (!empty($types)) {
                $placeholders = implode(',', array_fill(0, count($types), '?'));
                $sql .= " AND tt.type_name IN ($placeholders)";
                $params = array_merge($params, $types);
            }

            $sql .= " GROUP BY t.thesis_id ORDER BY t.pub_date DESC LIMIT 1000";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as &$row) {
                if (!empty($row['pub_date'])) {
                    $row['pub_date'] = date("F j, Y", strtotime($row['pub_date']));
                }
            }
            unset($row);

            foreach ($results as $index => &$row) {
                $row['number'] = $index + 1; 
            }
            unset($row);

            echo json_encode([
                "success" => true,
                "count" => count($results),
                "data" => $results
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $where = [];
        $params = [$q, $q, $q, $q, $q];

        $sql = "
            SELECT 
            t.thesis_id, 
            t.title,
            t.pub_place,
            t.pub_date,
            t.abstract,
            m.method_name AS methodology,
            t.visit_count,
            
                -- Get only the first author
            (
                SELECT CONCAT(a2.firstname, ' ', a2.middlename, ' ', a2.lastname)
                FROM Thesis_Authors ta2
                JOIN Authors a2 ON ta2.author_id = a2.author_id
                WHERE ta2.thesis_id = t.thesis_id
                ORDER BY ta2.author_id ASC
                LIMIT 1
            ) AS author,
            (
                MATCH(t.title, t.abstract) AGAINST (? IN NATURAL LANGUAGE MODE) +
                COALESCE(MAX(MATCH(k.keyword) AGAINST (? IN NATURAL LANGUAGE MODE)), 0) +
                CASE 
                    WHEN LOWER(t.title) = LOWER(?) THEN 100
                    WHEN LOWER(t.title) LIKE LOWER(CONCAT(?, '%')) THEN 50
                    WHEN LOWER(t.title) LIKE LOWER(CONCAT('%', ?, '%')) THEN 25
                    ELSE 0 
                END
            ) AS relevance
        FROM Thesis t
        LEFT JOIN Thesis_Keyword tk ON t.thesis_id = tk.thesis_id
        LEFT JOIN Keywords k ON tk.keyword_id = k.keyword_id
        JOIN Methodology m ON t.method_id = m.method_id
         LEFT JOIN Programs p ON t.program_id = p.program_id
        JOIN Thesis_Authors ta ON t.thesis_id = ta.thesis_id
        JOIN Authors a ON ta.author_id = a.author_id
        JOIN thesis_types tts on tts.thesis_id = t.thesis_id
        JOIN thesis_type tt on tt.type_id = tts.type_id

        ";

        if (!in_array(strtolower($filter), ['all', 'advance'])) {
            switch ($filter) {
                case 'keyword':
                    $where[] = "MATCH(k.keyword) AGAINST (? IN NATURAL LANGUAGE MODE)";
                    $params[] = $q;
                    break;
                case 'title':
                    $where[] = "MATCH(t.title) AGAINST (? IN NATURAL LANGUAGE MODE)";
                    $params[] = $q;
                    break;
                case 'author':
                    $where[] = "MATCH(a.firstname, a.middlename, a.lastname) AGAINST (? IN NATURAL LANGUAGE MODE)";
                    $params[] = $q;
                    break;
                default:
                    $where[] = "(MATCH(t.title, t.abstract) AGAINST (? IN NATURAL LANGUAGE MODE)
                                OR MATCH(k.keyword) AGAINST (? IN NATURAL LANGUAGE MODE))";
                    $params[] = $q;
                    $params[] = $q;
            }
        } else {
            $where[] = "(MATCH(t.title, t.abstract) AGAINST (? IN NATURAL LANGUAGE MODE)
                        OR MATCH(k.keyword) AGAINST (? IN NATURAL LANGUAGE MODE))";
            $params[] = $q;
            $params[] = $q;
        }

        if ($programs) {
            $where[] = "p.acronym IN (" . implode(',', array_fill(0, count($programs), '?')) . ")";
            $params = array_merge($params, $programs);
        }
        if ($methods) {
            $where[] = "m.method_name IN (" . implode(',', array_fill(0, count($methods), '?')) . ")";
            $params = array_merge($params, $methods);
        }
        if ($types) {
            $where[] = "tt.type_name IN (" . implode(',', array_fill(0, count($types), '?')) . ")";
            $params = array_merge($params, $types);
        }

        if ($where)
            $sql .= " WHERE " . implode(" AND ", $where);

        $sql .= "
                    GROUP BY t.thesis_id
                    ORDER BY relevance DESC
                    LIMIT 1000
                ";


        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as &$row) {
            if (!empty($row['pub_date'])) {
                $row['pub_date'] = date("F j, Y", strtotime($row['pub_date']));
            }
        }
        unset($row);

        foreach ($results as $index => &$row) {
            $row['number'] = $index + 1;
        }
        unset($row);


        echo json_encode([
            "success" => true,
            "count" => count($results),
            "data" => $results
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "error" => $e->getMessage()
        ]);
    }

    exit;
}
?>