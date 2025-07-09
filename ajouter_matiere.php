<?php

    use App\App;
    use App\Utils\Util;

    require_once "vendor/autoload.php";
    require_once "components/utils.php";
    App::start_session();
    $app = new App();

    if(!isset($_SESSION["user"]["manager"]) || !$_SESSION["user"]["manager"]) {
        header("location: ./index.php");
    }

    if (isset($_POST["name"], $_POST["coef"], $_POST['niveau'], $_POST['semestre']) || (isset($_POST["name"], $_POST["coef"], $_POST['niveau'], $_POST['semestre']) && ($use == 1 && isset($_POST['unity'])))) {

        if(Util::isEmpty($_POST["name"], $_POST["coef"], $_POST['niveau'], $_POST['semestre']) || 
        (Util::isEmpty($_POST["name"], $_POST["coef"], $_POST['niveau'], $_POST['semestre']) &&
        ($use == 1 && Util::isEmpty($_POST['unity'])))) {
            $message = "Veuillez remplir tous les champ";
        }
        else{
            $unity = $use == 1 ? htmlspecialchars($_POST["unity"]) : null ;
            $teacher = $useTeacher == 1 ? htmlspecialchars($_POST["responsable"]) : $_SESSION["user"]['id'] ;

            $data = [
                "name" => htmlspecialchars($_POST["name"]) ?? "",
                "respo" => $teacher,
                "unity" => $unity,
                "coef" => htmlspecialchars($_POST["coef"]) ?? "",
                "level" => htmlspecialchars($_POST['niveau']) ?? "",
                "semestre" => htmlspecialchars($_POST['semestre']) ?? ""
            ];

            if (!$message = $app->add_matiere($data)) {
                header("location:./ajouter_matiere.php?status");
            }
        }
        
        
    }
?>

<?php
    $page = "addMatiere";
    require_once "components/header.php";

?>

    <main class="site add-student" id="site">
        <div class="title flex">
            <h1>Ajouter une matiere</h1>
        </div>
        
        <form method="POST" action="./ajouter_matiere.php" class="form flex wrap">
            
            
            <div class="leftpart">
                <div class="form-items flex-column">
                    <table>
                        <div class="form-item flex">
                            <tr>
                                <td>
                                    <label for="name">Matière</label> 
                                </td>
                                <td>
                                    <label for="name" class="content-input">
                                        <input type="text" name="name" id="name" placeholder="Mathématique">
                                    </label>
                                </td>
                            </tr>
                            <?php if($useTeacher == 1): ?>
                            <tr>
                                <td>
                                    <label for="responsable">Enseignant</label> 
                                </td>
                                <td>
                                    <label for="responsable" class="content-input">
                                        <select name="responsable" id="responsable">
                                            <?php foreach ($teachers as $teacher) {
                                               echo Util::genOptionTeacher($teacher);
                                            } ?>
                                        </select>
                                    </label>
                                </td>
                            </tr>

                            <?php endif;?>
                            <?php if($use == 1) :?>
                            <tr>
                                <td>
                                    <label for="unity">Unité E.</label> 
                                </td>
                                <td>
                                    <label for="unity" class="content-input">
                                        <select name="unity" id="unity" >
                                            <?php foreach ($unities as $unity) {
                                               echo Util::genOptionUnity($unity);
                                            } ?>
                                        </select>
                                    </label>
                                </td>
                            </tr>

                            <?php endif;?>
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
                                    <label for="coef">Coéfficient</label>
                                    
                                </td>
                                <td>
                                    <label for="coef" class="content-input">
                                        <input type="number" name="coef" id="coef">
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="niveau">Niveau</label> 
                                </td>
                                <td>
                                    <label for="niveau" class="content-input">
                                        <select name="niveau" id="niveau" >
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                        </select>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="semestre">Semestre</label> 
                                </td>
                                <td>
                                    <label for="semestre" class="content-input">
                                        <select name="semestre" id="semestre" >
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                        </select>
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

    </div>
</body>
</html>