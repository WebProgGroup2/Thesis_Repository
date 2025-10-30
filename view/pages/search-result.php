<?php
$BASE_URL = "http://" . $_SERVER['HTTP_HOST'] . "/thesis_repo/";
$searchTerm = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';

require_once __DIR__ . '/../../config/connection.php';


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

  <link rel="stylesheet" href="../css/root.css">
  <link rel="stylesheet" href="../css/body-format.css" />
  <link rel="stylesheet" href="../css/search-result.css" />
  <link rel="stylesheet" href="../css/sidebar.css" />
  <link rel="stylesheet" href="../css/search-bar.css" />
  <link rel="stylesheet" href="../css/account-toggle.css" />
  <link rel="stylesheet" href="../css/footer.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>

<body>
  <div class="content-wrapper">
    <div id="sidebar-spot"></div>

    <div class="main">
      <div class="main-sections" id="header">

      </div>

      <div class="main-sections" id="main-content">
        <div class="title-section">
          <div class="title-wrapper">
            <span class="material-symbols-outlined title-icon">description</span>
            <span>Results for "<?php echo $searchTerm; ?>"</span>
          </div>

          <!-- Dropdown -->
          <div class="dropDown-filter">
            <label for="year-filter">Filter by Year:</label>
            <select id="year-filter" name="year">
              <option value="">All Years</option>
              <?php foreach ($years as $year): ?>
                <option value="<?= htmlspecialchars($year) ?>"><?= htmlspecialchars($year) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="result-wrapper" id="result-wrapper">
          <!-- Results injected here -->
        </div>

        <div class="pagination-wrapper">
          <div class="buttons-wrapper" id="pagination-buttons">
            <!-- Pagination buttons injected here -->
          </div>
        </div>

      </div>

    </div>
  </div>
  <script>
    const baseURL = "<?php echo $BASE_URL; ?>";
  </script>
  <script src="../js/sidebar.js"></script>
  <script src="../js/search-result.js"></script>
  <script src="../js/search-bar.js"></script>
  <script src="../js/account-toggle.js"></script>
  <script src="../js/footer.js"></script>
</body>

</html>