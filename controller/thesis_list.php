<?php
require_once "connection.php";
?>
<h4>Thesis</h4>
    <div class="row">
    <?php
    // Function to print thesis image
    function printThesisImage($filename) {
        if ($filename && file_exists(__DIR__ . '/../view/uploads/image_upload/' . $filename)) {
            echo '<img src="view/uploads/image_upload/' . ($filename) . '" alt="Thesis Image" style="max-width:200px;">';
        } else {
            echo '<span>No image</span>';
        }
    }
    $resultThesis = $connection->query("SELECT thesis_id, title, author, abstract, publication_date, publication_place, subject, list, advance, keyword, thesis_image, pdf_filename FROM thesis_table ORDER BY thesis_id DESC");
    while ($row = $resultThesis->fetch_assoc()) {
        echo '<div class="col-md-3 mb-3">';
        echo '<strong>Title:</strong> ' . ($row['title']) . '<br>';
        echo '<strong>Author:</strong> ' . ($row['author']) . '<br>';
        echo '<strong>Abstract:</strong> ' . ($row['abstract']) . '<br>';
        echo '<strong>Publication Date:</strong> ' . ($row['publication_date']) . '<br>';
        echo '<strong>Publication Place:</strong> ' . ($row['publication_place']) . '<br>';
        echo '<strong>Subject:</strong> ' . ($row['subject']) . '<br>';
        echo '<strong>List:</strong> ' . ($row['list']) . '<br>';
        echo '<strong>Advance:</strong> ' . ($row['advance']) . '<br>';
        echo '<strong>Keyword:</strong> ' . ($row['keyword']) . '<br>';
        printThesisImage($row['thesis_image']);
        if (!empty($row['pdf_filename'])) {
            echo '<br><a href="view/uploads/pdf_upload/' . ($row['pdf_filename']) . '" target="_blank" class="btn btn-sm btn-success mt-2">View PDF</a>';
        }
        echo '</div>';
    }
    ?>
    </div>