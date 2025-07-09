<?php
    use App\App;
    use App\Utils\Util;

    require_once "vendor/autoload.php";
    App::start_session();

    if(isset($_SESSION["user"])) {
        
    }
?>

<?php
    $page = "settings";
    require_once "components/header.php";
?>

    <main class="site" id="site">
        <div class="title flex">
            <h1>Parametres</h1>
        </div>
        <div class="wrapper">
            <div class="ue flex">
                <div class="toggle-UE flex align-center">
                    <span>Unités d'enseignement</span>
                    <a href="./settings.php?ua" class="flex align-center">
                        <div class="content-mover <?=$toggle_class?>">
                            <div class="mover"></div>
                        </div>
                    </a>
                </div>
    
            </div>
            <div class="ue flex">
                <div class="toggle-UE flex align-center">
                    <span>Enseignant par matiere</span>
                    <a href="./settings.php?teacher" class="flex align-center">
                        <div class="content-mover <?=$toggle_class_teacher?>">
                            <div class="mover"></div>
                        </div>
                    </a>
                </div>
    
            </div>
        </div>
    </main>

    </div>
</body>
</html>