<?php

    use App\App;

    require_once "vendor/autoload.php";
    App::start_session();

    $app = new App();
    $teachers = $app->get_teachers();
    $unities = $app->get_unities();
    $message = "";

    $toggle_class = "";
    $toggle_class_teacher = "";
    $useTeacher = 0;
    $use = 0;
    $filepath = "assets/files/useUE.txt";
    $filepath_teacher = "assets/files/useTeacher.txt";

    try {
        $use = (int)file_get_contents($filepath, false, null, 0, 1);
        $toggle_class = $use == 1 ? "active" : "";
        
    } catch (Exception $e) {
        
    }
    
    if(isset($_GET["ua"])) {
        if ($use == 1) {
            file_put_contents($filepath, "0 \n Do not modify");
        } else {
            file_put_contents($filepath,"1 \n Do not modify");
        }
        $use = (int)file_get_contents($filepath, false, null, 0, 1);
        $toggle_class = $use == 1 ? "active" : "";
    }

    try {
        $useTeacher = (int)file_get_contents($filepath_teacher, false, null, 0, 1);
        $toggle_class_teacher = $useTeacher == 1 ? "active" : "";
        
    } catch (Exception $e) {
        
    }
    
    if(isset($_GET["teacher"])) {
        if ($useTeacher == 1) {
            file_put_contents($filepath_teacher, "0 \n Do not modify");
        } else {
            file_put_contents($filepath_teacher,"1 \n Do not modify");
        }
        $useTeacher = (int)file_get_contents($filepath_teacher, false, null, 0, 1);
        $toggle_class_teacher = $useTeacher == 1 ? "active" : "";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
    <!-- Stylesheet -->

    <script src="assets/app.js" defer></script>
    <!-- JS Script -->

    <link rel="stylesheet" href="assets/css/all.css">
    <!-- Font awesome -->
    <title>Notes Manager</title>
</head>
<body>
    <div class="wrapper flex">

        <header class="leftbar flex-column">
                <nav class="navbar">
                    <div class="top flex">
                        <svg id="Component_12_1" data-name="Component 12 – 1"   xmlns="http://www.w3.org/2000/svg" width="34" height="36" viewBox="0 0 34 36">
                            <g id="Ellipse_1" data-name="Ellipse 1" fill="#fff" stroke="#707070" stroke-width="1">
                                <circle cx="17" cy="17" r="17" stroke="none"/>
                                <circle cx="17" cy="17" r="16.5" fill="none"/>
                            </g>
                            <g id="Ellipse_2" data-name="Ellipse 2" transform="translate(3 3)" fill="rgba(228,228,228,0.74)" stroke="rgba(112,112,112,0.58)" stroke-width="1">
                                <circle cx="14" cy="14" r="14" stroke="none"/>
                                <circle cx="14" cy="14" r="13.5" fill="none"/>
                            </g>
                            <g id="Ellipse_3" data-name="Ellipse 3" transform="translate(17 19)" fill="#5761f3" stroke="#707070" stroke-width="1">
                                <circle cx="8.5" cy="8.5" r="8.5" stroke="none"/>
                                <circle cx="8.5" cy="8.5" r="8" fill="none"/>
                            </g>
                        </svg>


                        <a href="#" class="logo">School</a>
                    </div>

                    <div class="searchbar">
                        <label for="search" class="align-center">
                            <input type="text" name="search" id="search" placeholder="Rechercher un etudiant...">
                            <i class="fa fa-search"></i>
                        </label>
                    </div>

                    <div class="middle">
                        <div class="mini-title">
                            App
                        </div>

                        <div class="nav-links flex-column ">
                            <div class="nav-link flex <?= $page != "index" ?: "active" ?>">
                                <a href="./index.php" class="align-center">
                                    <i class="fa fa-home"></i>
                                    <label>Accueil</label>
                                </a>

                                <div class="overview ">
                                    Accueil
                                </div>
                            </div>
                            <div class="nav-link flex  <?= $page != "addStudent" ?: "active" ?>">
                                <a href="./ajouter_etudiant.php" class="align-center">
                                    <i class="fa fa-plus"></i>
                                    <label>Ajouter un etudiant</label>
                                </a>

                                
                                <div class="overview ">
                                    Etudiant
                                </div>
                            </div>
                            <div class="nav-link flex  <?= $page != "addMatiere" ?: "active" ?>">
                                <a href="./ajouter_matiere.php" class="align-center">
                                    <i class="fa fa-plus"></i>
                                    <label>Ajouter une matière</label>
                                </a>

                                
                                <div class="overview ">
                                    Matiere
                                </div>
                            </div>
                            <?php if(isset($use) && $use == 1):?>
                            <div class="nav-link flex  <?= $page != "addUnite" ?: "active" ?>">
                                <a href="./ajouter_unite.php" class="align-center">
                                    <i class="fa fa-plus"></i>
                                    <label>Ajouter une UE</label>
                                </a>

                                
                                <div class="overview ">
                                    UE
                                </div>
                            </div>
                            <?php endif?>
                            <div class="nav-link flex  <?= $page != "addNote" ?: "active" ?>">
                                <a href="./ajouter_note.php" class="align-center">
                                    <i class="fa fa-notes"></i>
                                    <label>Ajouter des notes</label>
                                </a>

                                
                                <div class="overview ">
                                    Notes
                                </div>
                            </div>
                            <div class="nav-link flex  <?= $page != "listStudent" ?: "active" ?>">
                                <a href="./liste_etudiants.php" class="align-center">
                                    <i class="fa fa-list"></i>
                                    <label>Liste des etudiants</label>
                                </a>

                                
                                <div class="overview ">
                                    Liste
                                </div>
                            </div>
                            <div class="nav-link flex <?= $page != "genBordereau" ?: "active" ?>">
                                <a href="./generer_bordereaux.php" class="align-center ">
                                    <i class="fa fa-file-pdf"></i>
                                    <label>Generer un bordereau</label>
                                </a>

                                
                                <div class="overview ">
                                    Bordereau
                                </div>
                            </div>
                        </div>

                        
                        <div class="mini-title">
                            Compte
                        </div>


                        <?php
                            if(isset($_SESSION["user"]) && !empty($_SESSION["user"])):
                        ?>
                        <div class="nav-links flex-column">
                            <div class="nav-link flex <?= $page != "settings" ?: "active" ?>">
                                <a href="./settings.php" class="align-center ">
                                    <i class="fa fa-gear"></i>
                                    <label>Paramètres</label>

                                    
                                    <div class="overview ">
                                        Parametres
                                    </div>
                                </a>
                            </div>
                        </div>
                        <?php
                           else:
                        ?>
                        <div class="nav-links flex-column">
                            <div class="nav-link flex <?= $page != "connect" ?: "active" ?>">
                                <a href="./connexion.php" class="align-center ">
                                    <i class="fa fa-sign-in"></i>
                                    <label>Se connecter</label>

                                    
                                    <div class="overview ">
                                        Connexion
                                    </div>
                                </a>
                            </div>
                            <div class="nav-link flex <?= $page != "register" ?: "active" ?>">
                                <a href="./inscription.php" class="align-center ">
                                    <i class="fa fa-user-plus"></i>
                                    <label>Creer un compte</label>
                                </a>

                                
                                <div class="overview ">
                                    Inscription
                                </div>
                            </div>
                        </div>
                        <?php
                           endif;
                        ?>

                    <div class="bottom">
                        <hr>
                        <?php if(isset($_SESSION["user"])) :?>
                        <div class="user-card align-center">
                            <a href="#" class="user-card align-center">

                                <div class="img-content">
                                    
                                </div>
                                <div class="user-info flex-column">
                                    <p class="username">J. Code</p>
                                    <p class="user-role">Programmation web et IA</p>
                                </div>
                                <a href="./disconnect.php" class="pointer">
                                    <i class="fa fa-sign-out logout"></i>
                                </a>
                            </a>
                        </div>
                        <?php endif ?>
                    </div>

                    <div class="header-modifier flex-center pointer">
                        <i class="fa fa-chevron-left"></i>
                    </div>
                </nav>
            </header>

            <?php if(isset($_GET['status'])):  ?>
            <div class="alert-info flex item-center">
                <i class="fa fa-x"></i>
                <p class="flex align-center">Operation reussie <span><img src="assets/img/img.jpg" alt=""></span>  </p>
            </div>

            <?php endif; ?>