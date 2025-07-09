<?php

    use App\App;

    require_once "vendor/autoload.php";
    App::start_session();
    unset($_SESSION["user"]);
    header("location:index.php");