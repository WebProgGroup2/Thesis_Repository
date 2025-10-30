<?php

$BASE_URL = "http://" . $_SERVER['HTTP_HOST'] . "/thesis_repo/";

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../controller/my_cookies.php';

requireLoginCookies();

try {
  $stmt = $conn->query("
        SELECT DISTINCT YEAR(pub_date) AS year
        FROM Thesis
        WHERE pub_date IS NOT NULL
        ORDER BY year DESC
    ");
  $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
  $years = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LRC THESIS REPOSITORY</title>

  <!-- Styles -->
  <link rel="stylesheet" href="../css/root.css" />
  <link rel="stylesheet" href="../css/body-format.css" />
  <link rel="stylesheet" href="../css/archive.css" />
  <link rel="stylesheet" href="../css/sidebar.css" />
  <link rel="stylesheet" href="../css/search-bar.css" />
  <link rel="stylesheet" href="../css/account-toggle.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>

<body>

  <div class="content-wrapper">
    <div id="sidebar-spot"></div>

    <div class="main">
      <div class="main-sections" id="header"></div>

      <div class="main-sections" id="main-content">
        <div class="main-header">
          <span>LIST OF CURRENT AVAILABLE THESIS</span>

          <!-- Dropdown -->
          <div class="modifier-section">
            <button id="add-btn">ADD</button>
            <button id="refresh-btn">REFRESH</button>
            <label for="year-filter">Filter by Year:</label>
            <select id="year-filter" name="year">
              <option value="">All Years</option>
              <?php foreach ($years as $year): ?>
                <option value="<?= htmlspecialchars($year) ?>"><?= htmlspecialchars($year) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="list-wrapper">
          <div class="list-header">
            <span class="header-info">TITLE</span>
            <span class="header-info">AUTHOR</span>
            <span class="header-info">PUBLICATION DATE</span>
            <span class="header-info">PUBLICATION PLACE</span>
            <span class="header-info">METHODOLOGY</span>
            <span class="header-info">ACTION BTN</span>
          </div>
          <div class="thesis-list" id="thesis-list">
            <!-- Results injected dynamically -->
          </div>
        </div>
        <div id="pop-up">
          <form id="thesis-form" enctype="multipart/form-data">

            <!-- ==================== FORM HEADER ==================== -->
            <div id="form-header">
              <span id="purpose-label">Purpose Placeholder</span>
              <div id="form-btns">
                <!-- Submit button -->
                <button type="submit" id="submit-thesis">Purpose Thesis</button>
                <!-- Close button -->
                <button type="button" id="close-popup">
                  <span class="material-symbols-outlined">close</span>
                </button>
              </div>
            </div>

            <!-- ==================== FORM BODY ==================== -->
            <div id="form-body">

              <!-- ==================== LEFT FORM ==================== -->
              <div class="form-section" id="left-form">

                <!-- Thesis Title -->
                <div class="form-group input-textfields" id="title-field">
                  <label for="title-textfield" class="labels">Thesis Title <span style="color:red">*</span></label>
                  <input type="text" id="title-textfield" name="thesis-title" class="textfields" required>
                </div>

                <!-- Cover Image -->
                <div class="form-group" id="img-upload">
                  <label for="cover-img" class="upload-box">
                    <img id="img-preview" alt="Image Preview" class="img-preview">
                    <span id="upload-text">Upload Thesis Cover <span style="color:red">*</span></span>
                  </label>
                  <input type="file" id="cover-img" name="thesis-cover" accept="image/*" required hidden>
                </div>

                <!-- PDF Upload -->
                <div class="form-group" id="pdf-upload">
                  <label for="thesis-file" id="upload-label">
                    <button id="add-file-btn" type="button">
                      <span class="material-symbols-outlined">add</span>
                    </button>
                    <span id="file-name" class="no-click">Add thesis' PDF file <span style="color:red">*</span></span>
                  </label>
                  <input type="file" id="thesis-file" name="thesis-file" accept="application/pdf" required hidden>
                </div>

                <!-- Authors -->
                <div class="form-group input-textfields" id="author-field">
                  <label for="author-textfield" class="labels">Author and Co-authors <span
                      style="color:red">*</span></label>
                  <input type="text" id="author-fName" name="author-name" class="textfields author-field"
                    placeholder="Firstname" required>
                  <input type="text" id="author-mName" name="author-name" class="textfields author-field"
                    placeholder="Middlename (if applicable)">
                  <input type="text" id="author-lName" name="author-name" class="textfields author-field"
                    placeholder="Lastname" required>
                  <div class="input-list" id="author-list"></div>
                </div>

                <!-- Publication Date -->
                <div class="form-group" id="date-field">
                  <label for="pub-date" class="labels">Publication Date <span style="color:red">*</span></label>
                  <input type="date" id="pub-date" name="pub-date" class="date-picker" required>
                </div>

                <!-- Publication Place -->
                <div class="form-group input-textfields" id="place-field">
                  <label for="place-textfield" class="labels">Publication Place <span style="color:red">*</span></label>
                  <input type="text" id="place-textfield" name="pub-place" class="textfields" required>
                </div>

              </div> <!-- End Left Form -->

              <!-- ==================== RIGHT FORM ==================== -->
              <div class="form-section" id="right-form">

                <!-- Abstract -->
                <div class="form-group" id="abstract-field">
                  <label for="abstract-editor" class="labels">Abstract <span style="color:red">*</span></label>
                  <div class="editor-toolbar">
                    <button type="button" data-command="bold"><b>B</b></button>
                    <button type="button" data-command="italic"><i>I</i></button>
                    <button type="button" data-command="underline"><u>U</u></button>
                  </div>
                  <div id="abstract-editor" contenteditable="true" class="editor-area"
                    placeholder="Write the abstract here..." required></div>
                </div>

                <!-- Methodology & Thesis Type -->
                <div class="form-group" id="selection-field">
                  <!-- Methodology -->
                  <div class="thesis-selection" id="methodology-section">
                    <label for="methodology-selection">Methodology <span style="color:red">*</span></label>
                    <select id="methodology-selection" class="form-control" required>
                      <option value="">-- Select Methodology --</option>
                    </select>
                  </div>

                  <!-- Thesis Types -->
                  <div class="thesis-selection" id="type-section">
                    <label>Thesis Type <span style="color:red">*</span></label>
                    <div id="type-checkboxes" class="checkbox-group"></div>
                  </div>
                </div>

                <!-- Keywords -->
                <div class="form-group input-textfields input-list-field" id="keyword-field">
                  <label for="keyword-textfield" class="labels">Keywords <span style="color:red">*</span></label>
                  <input type="text" id="keyword-textfield" name="keyword-name" class="textfields"
                    placeholder="Enter keyword and press Enter" required>
                  <div class="input-list" id="keyword-list"></div>
                </div>

                <!-- References -->
                <div class="form-group input-textfields input-list-field" id="reference-field">
                  <label for="reference-textfield" class="labels">References <span style="color:red">*</span></label>
                  <input type="text" id="reference-textfield" name="reference" class="textfields"
                    placeholder="Enter reference and press Enter" required>
                  <div class="input-list" id="reference-list"></div>
                </div>

                <!-- Page Count -->
                <div class="form-group input-textfields" id="pagecount-field">
                  <label for="pagecount-textfield" class="labels">Page Count <span style="color:red">*</span></label>
                  <input type="number" id="pagecount-textfield" name="page-count" class="textfields" min="1"
                    placeholder="Enter number of pages" required>
                </div>

                <!-- Program -->
                <div class="form-group thesis-selection" id="program-section">
                  <label for="program-selection">Program <span style="color:red">*</span></label>
                  <select id="program-selection" class="form-control" required>
                    <option value="">-- Select Program --</option>
                  </select>
                </div>

              </div> <!-- End Right Form -->

            </div> <!-- End Form Body -->

          </form>
        </div>

      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    const baseURL = "<?php echo $BASE_URL; ?>";
  </script>
  <script src="<?php echo $BASE_URL; ?>View/js/archive.js?v=<?php echo time(); ?>"></script>
  <script src="<?php echo $BASE_URL; ?>View/js/search-bar.js?v=<?php echo time(); ?>"></script>
  <script src="<?php echo $BASE_URL; ?>View/js/sidebar.js?v=<?php echo time(); ?>"></script>
  <script src="<?php echo $BASE_URL; ?>View/js/account-toggle.js?v=<?php echo time(); ?>"></script>
</body>

</html>