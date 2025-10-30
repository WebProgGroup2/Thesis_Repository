<?php
session_start();


$thesis_id = $_GET['thesis_id'] ?? null;
$file = $_GET['file'] ?? null;

if (!$thesis_id || !$file) {
    die("Invalid request.");
}


$file = basename($file); 


$filePath = __DIR__ . "/../uploads/pdfs/$file";

if (!file_exists($filePath) || pathinfo($filePath, PATHINFO_EXTENSION) !== 'pdf') {
    die("PDF not found.");
}


$fileUrl = "/thesis_repo/uploads/pdfs/" . urlencode($file);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Thesis Viewer</title>
    <style>
        html,
        body {
            margin: 0;
            height: 100%;
            overflow: auto;
           
            background: #f0f0f0;
          
        }

        #viewerContainer {
            display: flex;
            flex-direction: column;
          
            align-items: center;
           
            padding: 20px;
        }

        canvas {
            display: block;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }


        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.10.111/pdf.min.js"></script>

</head>

<body>
    <div id="viewerContainer"></div>
    <script>
        const url = <?php echo json_encode($fileUrl); ?>;

        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.10.111/pdf.worker.min.js';

        const container = document.getElementById('viewerContainer');

        async function renderPDF(url) {
            const loadingTask = pdfjsLib.getDocument(url);
            const pdf = await loadingTask.promise;

            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                const page = await pdf.getPage(pageNum);
                const viewport = page.getViewport({ scale: 1.5 });

                const canvas = document.createElement('canvas');
                container.appendChild(canvas);

                canvas.width = viewport.width;
                canvas.height = viewport.height;

                const context = canvas.getContext('2d');
                await page.render({ canvasContext: context, viewport }).promise;
            }
        }

        renderPDF(url);

        window.addEventListener('contextmenu', e => e.preventDefault());
        window.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                alert('Printing is disabled.');
            }
        });
    </script>
</body>

</html>