<?php
    $localhost = "localhost";
    $username = "root";
    $pw = "";
    $dbName = "thesis_repository";

    $connection = mysqli_connect($localhost,  $username, $pw, $dbName );
    
    if($connection == true){
        echo "Connected";
    }

?>