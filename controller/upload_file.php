<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coverUploadDir = __DIR__ . '/../uploads/covers/';
    $pdfUploadDir = __DIR__ . '/../uploads/pdfs/';


    if (!file_exists($coverUploadDir) || !is_dir($pdfUploadDir)) {
        mkdir($coverUploadDir, 0777, true);
        mkdir($pdfUploadDir, 0777, true);
    }

    $responses = [];

    if (isset($_FILES['thesis-cover']) && $_FILES['thesis-cover']['error'] === UPLOAD_ERR_OK) {
        $tmpPath = $_FILES['thesis-cover']['tmp_name'];
        $ext = pathinfo($_FILES['thesis-cover']['name'], PATHINFO_EXTENSION);
        $newName = 'cover_' . time() . '.' . $ext;
        $destPath = $coverUploadDir . $newName;

        if (move_uploaded_file($tmpPath, $destPath)) {
            $responses['cover'] = 'uploads/covers/' . $newName;
        } else {
            $responses['cover_error'] = 'Failed to upload cover image.';
        }
    }

    if (isset($_FILES['thesis-file']) && $_FILES['thesis-file']['error'] === UPLOAD_ERR_OK) {
        $tmpPath = $_FILES['thesis-file']['tmp_name'];
        $ext = pathinfo($_FILES['thesis-file']['name'], PATHINFO_EXTENSION);
        $newName = 'thesis_' . time() . '.' . $ext;
        $destPath = $pdfUploadDir . $newName;

        if (move_uploaded_file($tmpPath, $destPath)) {
            $responses['pdf'] = 'uploads/pdfs/' . $newName;
        } else {
            $responses['pdf_error'] = 'Failed to upload thesis PDF.';
        }
    }

    header('Content-Type: application/json');
    echo json_encode($responses);
}
?>