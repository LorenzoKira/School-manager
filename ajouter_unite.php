<?php

    use App\App;
    use App\Utils\Util;

    require_once "vendor/autoload.php";
    App::start_session();

    if(!isset($_SESSION["user"]["manager"]) || !$_SESSION["user"]["manager"]) {
        header("location: ./index.php");
    }

    $app = new App();
    $message = "";

    if (isset($_POST["name"], $_POST["credit"])) {
        if(Util::isEmpty($_POST["name"], $_POST["credit"])) {
            $message = "Veuillez remplir tous les champ";
        }
        else{

            $data = [
                "name" => htmlspecialchars($_POST["name"]) ?? "",
                "credit" => htmlspecialchars($_POST["credit"]) ?? ""
            ];

            if (!$message = $app->add_unite($data)) {
                header("location:./ajouter_unite.php?status");
            }
        }
        
        
    }
?>

<?php
    $page = "addUnite";
    require_once "components/header.php";

?>

<?php if($use == 1):?>

    <main class="site add-student" id="site">
        <div class="title flex">
            <h1>Ajouter une unité d'enseignement</h1>
            <div class="toggle-UE">
                <a href="./ajouter_unite.php?ua" class="flex align-center">
                    <span>Unités d'enseignement</span>
                    <div class="content-mover <?=$toggle_class?>">
                        <div class="mover"></div>
                    </div>
                </a>
            </div>
        </div>
        
        <form method="POST" action="./ajouter_unite.php" class="form flex wrap">
            
            
            <div class="leftpart">
                <div class="form-items flex-column">
                    <table>
                        <div class="form-item flex">
                            <tr>
                                <td>
                                    <label for="name">Nom Unité</label> 
                                </td>
                                <td>
                                    <label for="name" class="content-input">
                                        <input type="text" name="name" id="name" placeholder="Mathématique">
                                    </label>
                                </td>
                            </tr>
                        </div>
                    </table>
                </div>
                <p style="color: red; font-size: 12px;"><?=$message?></p>
            </div>
                
                <hr class="desktop-hide">
                <div class="rightpart">
                <div class="form-items flex-column">
                    <table>
                        <div class="form-item flex">
                            <tr>
                                <td>
                                    <label for="credit">Crédits</label>
                                    
                                </td>
                                <td>
                                    <label for="credit" class="content-input">
                                        <input type="number" name="credit" id="credit">
                                    </label>
                                </td>
                            </tr>
                    </table>
                    </div>

                    <button type="submit" class="radius-3 submit" >Ajouter</button>
                </div>
                </div>

        </form>
    </main>

<?php else:?>
    <main class="site add-student" id="site">
            <div class="title flex">
                <h1>Ajouter une unité d'enseignement</h1>
                <div class="toggle-UE">
                <a href="./ajouter_unite.php?ua" class="flex align-center">
                    <span>Unités d'enseignement</span>
                    <div class="content-mover <?=$toggle_class?>">
                        <div class="mover"></div>
                    </div>
                </a>
            </div>
        </div>
        <div class="alternative-main flex-column flex-center ">
    
    
            <h2 class="alert-alert">Veuillez activer les unités d'enseignement en premier</h2>
            <div class="central-div">
                <img src="./assets/img/error.jpg" alt="">
            </div>
    
        </div>
    </main>

<?php endif;?>

    </div>
</body>
</html>