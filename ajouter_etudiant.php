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

    if (isset($_POST["firstname"], $_POST["lastname"], $_POST["birth"], $_POST["place"], $_POST["serie"], $_POST["level"], $_POST["class"])) {
        if(Util::isEmpty($_POST["firstname"], $_POST["lastname"], $_POST["birth"], $_POST["place"], $_POST["serie"], $_POST["level"], $_POST["class"])) {
            $message = "Veuillez remplir tous les champ";
        }
        else{
            $data = [
                "name"    => htmlspecialchars($_POST["firstname"]) ?? "",
                "surname" => htmlspecialchars($_POST["lastname"]) ?? "",
                "birth"   => htmlspecialchars($_POST["birth"]) ?? "",
                "place"   => htmlspecialchars($_POST["place"]) ?? "",
                "serie"   => htmlspecialchars($_POST["serie"]) ?? "",
                "level"   => htmlspecialchars($_POST["level"]) ?? "",
                "class"   => htmlspecialchars($_POST["class"]) ?? ""
            ];

            if (!$message = $app->add_student($data)) {
                header("location:index.php");
            }
        }
        
        
    }
?>


<?php
    $page = "addStudent";
    require_once "components/header.php";
?>

    <main class="site add-student" id="site">
        <div class="title">
            <h1>Ajouter un etudiant</h1>
        </div>
        
        <form method="POST" class="form flex wrap">
            
            
            <div class="leftpart">
                <div class="form-items flex-column">
                    <table>
                        <div class="form-item flex">
                            <tr>
                                <td>
                                    <label for="firstname">Nom</label>
                                    
                                </td>
                                <td>
                                    <label for="firstname" class="content-input">
                                        <input type="text" name="firstname" id="firstname">
                                    </label>
                                </td>
                            </tr>
                        </div>
                        <div class="form-item flex">
                            <tr>
                                <td>
                                    <label for="lastname">Prenom</label>
                                </td>
                                <td>
                                    <label for="lastname" class="content-input">
                                        <input type="text" name="lastname" id="lastname">
                                    </label>
                                </td>
                            </tr>
                        </div>
                        <div class="form-item flex">
                            <tr>
                                <td>
                                    <label for="birth">Naissance</label>
                                </td>
                                <td>
                                    <label for="birth" class="content-input">
                                        <input type="date" name="birth" id="birth">
                                    </label>
                                </td>
                            </tr>
                        </div>
                        <div class="form-item flex">
                            <tr>
                                <td>
                                    <label for="place">Lieu</label>
                                </td>
                                <td>
                                    <label for="place" class="content-input">
                                        <select name="place" id="place">
                                            <option value="Douala">Douala</option>
                                            <option value="Douala">Yaounde</option>
                                        </select>
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
                                    <label for="serie">Filiere</label>
                                    
                                </td>
                                <td>
                                    <label for="serie" class="content-input">
                                        <select name="serie" id="serie">
                                            <option value="SR">SR</option>
                                            <option value="GL">GL</option>
                                        </select>
                                    </label>
                                </td>
                            </tr>
                        </div>
                        <div class="form-item flex">
                            <tr>
                                <td>
                                    <label for="level">Niveau</label>
                                </td>
                                <td>
                                    <label for="level" class="content-input">
                                        <select name="level" id="level">
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                        </select>
                                    </label>
                                </td>
                            </tr>
                        </div>
                        <div class="form-item flex">
                            <tr>
                                <td>
                                    <label for="class">Classe</label>
                                </td>
                                <td>
                                    <label for="class" class="content-input">
                                        <select name="class" id="class">
                                            <option value="A">A</option>
                                            <option value="B">B</option>
                                        </select>
                                    </label>
                                </td>
                            </tr>
                        </div>
                    </table>
                </div>
                <button type="submit" class="radius-3 submit">Envoyer</button>
            </div>

        </form>
    </main>

    </div>
</body>
</html>