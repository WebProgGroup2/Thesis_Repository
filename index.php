<?php
$BASE_URL = "http://" . $_SERVER['HTTP_HOST'] . "/thesis_repo/";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LRC THESIS REPOSITORY</title>
  <link rel="stylesheet" href="view/css/root.css">
  <link rel="stylesheet" href="view/css/body-format.css" />
  <link rel="stylesheet" href="view/css/index.css" />
  <link rel="stylesheet" href="view/css/sidebar.css" />
  <link rel="stylesheet" href="view/css/search-bar.css" />
  <link rel="stylesheet" href="view/css/account-toggle.css" />
  <link rel="stylesheet" href="view/css/footer.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>

<body>
  <div class="content-wrapper">
    <!-- SIDEBAR WILL RENDER HERE-->
    <div id="sidebar-spot">
      <!--Because sidebar is absolute we need something that will adjust the main so it can be visible from 2vw-->
    </div>
    <div class="main">
      <div class="main-sections" id="header">
        <!--SEARCH BAR WILL RENDER HERE-->
      </div>
      <div class="main-sections" id="main-content">
        <div class="title-wrapper">
          <span class="title">FIND 100+ OF REFERENCE FOR YOUR THESIS</span>
        </div>
        <div class="subtitle-wrapper">
          <span class="subtitle">ON-GOING / UPCOMING EVENTS</span>
        </div>
        <div class="carousel-wrapper">
          <div class="carousel" id="event-carousel"></div>
        </div>

        <div class="buttons-wrapper">
          <button class="help-btn" id="resourse-prob">
            REPORT AN E-RESOURCE PROBLEM
          </button>
          <button class="help-btn" id="assistance">
            REQUEST FOR ASSISTANCE
          </button>
          <button class="help-btn" id="about">ABOUT THESIS REPOSITORY</button>
        </div>
      </div>

    </div>
  </div>
  <script>
    const baseURL = "<?php echo $BASE_URL; ?>";
  </script>
  <script type="text/javascript" src="view/js/index.js"></script>
  <script type="text/javascript" src="view/js/sidebar.js"></script>
  <script type="text/javascript" src="view/js/search-bar.js"></script>
  <script type="text/javascript" src="view/js/account-toggle.js"></script>
  <script type="text/javascript" src="view/js/footer.js"></script>
</body>

</html>