<?php
$BASE_URL = "http://" . $_SERVER['HTTP_HOST'] . "/thesis_repo/";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LRC THESIS REPOSITORY</title>

    <link rel="stylesheet" href="../css/root.css">
    <link rel="stylesheet" href="../css/body-format.css" />
    <link rel="stylesheet" href="../css/view-thesis.css" />
    <link rel="stylesheet" href="../css/sidebar.css" />
    <link rel="stylesheet" href="../css/search-bar.css" />
     <link rel="stylesheet" href="../css/footer.css" />
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
                <div class="content-wrapper" id="upper-content">
                    <div class="columns" id="book-cover">

                    </div>
                    <div class="columns" id="initial-info">
                        <div class="row" id="rating-wrapper">
                            <span>Rate this Article</span>
                            <div class="stars-wrapper">
                                <span class="material-symbols-outlined" data-value="1">
                                    star
                                </span>
                                <span class="material-symbols-outlined" data-value="2">
                                    star
                                </span>
                                <span class="material-symbols-outlined" data-value="3">
                                    star
                                </span>
                                <span class="material-symbols-outlined" data-value="4">
                                    star
                                </span>
                                <span class="material-symbols-outlined" data-value="5">
                                    star
                                </span>
                            </div>
                        </div>
                        <div class="row" id="title-wrapper">
                            <span id="thesis-title">THESIS TITLE</span>
                        </div>
                        <div class="row" id="author-wrapper">
                            <span class="authors">Author 1</span>
                        </div>
                        <div class="row" id="pub-date-wrapper">
                            <span id="pub-date">October 13, 2004</span>
                        </div>
                    </div>
                    <div class="columns" id="nav-controls">
                        <button class="nav-btn prev-btn">
                            <span class="material-symbols-outlined">
                                arrow_upward
                            </span>
                        </button>
                        <div class="number-wrapper">
                            <div id="thesis-number">1</div>
                        </div>
                        <button class="nav-btn next-btn">
                            <span class="material-symbols-outlined">
                                arrow_downward
                            </span>
                        </button>
                    </div>
                </div>
                <div class="content-wrapper" id="lower-content">
                    <div class="thesis-sections" id="left-section">
                        <div class="thesis-cover-wrapper">
                            <img src="../assets/placeholder.jpg" alt="thesis-cover" id="thesis-cover">
                        </div>
                        <div id="upper-section">
                            <div></div>
                            <div id="button-wrapper">
                                <button id="read-thesis-btn">READ NOW</button>
                            </div>
                        </div>
                        <div id="lower-section">
                            <div class="wrapper" id="abstract-wrapper">
                                <span class="title" id="abstract">ABSTRACT</span>
                                <span class="content" id="abstract-content">
                                    Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem
                                    Ipsum has been the industry's standard dummy text ever since the 1500s, when an
                                    unknown printer took a galley of type and scrambled it to make a type specimen book.
                                    It has survived not only five centuries,but also the leap into electronic
                                    typesetting, remaining essentially unchanged. It was popularised in the 1960s with
                                    the release of Letraset sheets.
                                </span>
                            </div>
                            <div class="wrapper" id="methodology-wrapper">
                                <span class="title" id="methodology">METHODOLOGY</span>
                                <span class="content" id="methodology-content">
                                    Qualitative
                                </span>
                            </div>
                            <div class="wrapper" id="thesis-type-wrapper">
                                <span class="title" id="thesis-type">THESIS TYPE</span>
                                <!--When converted into js this will be relative to the number of results-->
                                <span class="content" id="thesis-type-content">Experimental, Correlational,
                                    Survey</span>
                            </div>
                            <div class="wrapper" id="keywords-wrapper">
                                <span class="title" id="keywords">KEYWORDS</span>
                                <!--When converted into js this will be relative to the number of results-->
                                <span class="content" id="keywords-content">consumer behavior security, crop modeling,
                                    cryptography</span>
                            </div>
                        </div>
                    </div>
                    <div class="thesis-sections" id="right-section">
                        <div class="wrapper">
                            <span class="label" id="co-author-label">Co-Author</span>
                            <div class="list">
                                <!--When converted into js this will be relative to the number of results-->
                                <span class="name">Johanson</span>
                                <span class="name">Michelle</span>
                                <span class="name">Cabrera</span>
                            </div>
                        </div>
                        <div class="wrapper">
                            <span class="label" id="publication-label">Publication Place</span>
                            <span id="place">NU LAGUNA</span>
                        </div>
                        <div class="wrapper">
                            <span class="label" id="references-label">Refrences</span>
                            <div class="list">
                                <!--When converted into js this will be relative to the number of results-->
                                <span class="reference">Reference 1</span>
                                <span class="reference">Reference 2</span>
                                <span class="reference">Reference 3</span>
                                <span class="reference">Reference 4</span>
                            </div>
                        </div>

                        <div id="page-wrapper">
                            <span id="page-number">Page count: 200</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const baseURL = "<?php echo $BASE_URL; ?>";
    </script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/search-bar.js"></script>
    <script src="../js/view-thesis.js"></script>
    <script src="../js/footer.js"></script>
</body>

</html>