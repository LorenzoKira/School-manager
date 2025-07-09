<?php
    use App\App;
    use App\Utils\Util;

    require_once "vendor/autoload.php";
    App::start_session();

    if(!isset($_SESSION["user"]) && !$_SESSION["user"]) {
        header("location: ./index.php");
    }

    $app = new App();
    $students = $app->get_students();
    $matieres = $app->get_matiere_by_teacher_id($_SESSION["user"]["id"], $_SESSION["user"]["manager"]);
    $student = false;
    $studentNotes = null;
    $result = "";
    $note = 0;

    if(isset($_GET['id']) && !empty($_GET["id"])) {
        $id = (int)htmlspecialchars($_GET["id"]);
        $student = $app->get_student_by_id($id);

    }

    if(!empty($_POST)) {
        $matiereId = [];

        if($student) {
           
            foreach ($matieres as $matiere) {
                $key = str_replace(" ", "_", $matiere->nomMatiere);

                if($_SESSION["user"]["manager"]) {

                }

                elseif(!isset($_POST[$key]) || empty($_POST[$key])) {
                    $result = "<p style='color: red; font-size: 12px;'>Veuillez remplir tous les champs</p>";
                    break;
                }

                $matiereId[strtolower($key)] = $matiere->idMatiere;
            }

            if(!$result) {
                $data = [];

                foreach($_POST as $key => $value) {
                    $data[strtolower($key)] = [
                        "note" => (int)htmlspecialchars($value != "" ? $value : 0) ?? 0,
                        "id" => $student->idEtudiant,
                        "idTeacher" => $_SESSION["user"]["id"]
                    ];
                }

                foreach($matiereId as $key => $value) {
                    $data[$key]["idMatiere"] = $value;
                }


                //
                $result = $app->add_notes($data, $_SESSION["user"]["manager"]);
            } 
        }else {
            $result = "<p style='color: red; font-size: 12px;'>Choisissez un etudiant</p>";
        }
    }

?>

<?php
    $page = "addNote";
    require_once "components/header.php";
?>



    <main class="site add-student flex " id="site">
        <main class="site add-student no-ml" id="site">
                
            <div class="title flex">
                <h1>Ajouter des notes</h1>
                
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
                                            <input type="text" name="firstname" id="firstname" value="<?=$student->nom ?? ""?>" disabled>
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
                                            <input type="text" name="firstname" id="lastname" value="<?=$student->prenom ?? ""?>" disabled>
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
                                        <label for="birth" class="content-input" id="date">
                                            <input type="date" name="birth" id="birth" value="<?=$student->dateNaissance ?? ""?>" disabled>
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
                                            <select name="place" id="place" disabled>
                                                <option value="<?=$student->lieuNaissance ?? ""?>" ><?=$student->lieuNaissance ?? ""?></option>
                                            </select>
                                        </label>
                                    </td>
                                </tr>
                            </div>
                        </table>
                    </div>
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
                                            <input type="text" name="serie" id="serie" value="<?=$student->filiere ?? ""?>" disabled>
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
                                            <select name="level" id="level" disabled>
                                                <option value="<?=$student->niveau ?? ""?>"><?=$student->niveau ?? ""?></option>
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
                                            <select name="class" id="class" disabled>
                                                <option value="<?=$student->classe ?? ""?>"><?=$student->classe ?? ""?></option>
                                            </select>
                                        </label>
                                    </td>
                                </tr>
                            </div>
                        </table>
                    </div>
                </div>

            </form>

            <form method="POST" class="form flex wrap">
                
                
                <div class="leftpart">
                    <div class="form-items flex-column">
                        <table>
                            <?php
                                foreach ($matieres as $matiere) {

                                    $note = $app->get_note_by_student_id($student->idEtudiant ?? 0, $matiere->idMatiere);


                                    echo Util::showMatiereByTeacher($matiere, $note->note ?? -20);
                                }
                            ?>
                        </table>
                    </div>
                </div>
                    
                <hr class="desktop-hide">
                <div class="rightpart">
                    <div class="form-items flex-column">
                        <table>
                        </table>
                    </div>

                    
                    <button type="submit" class="radius-3 submit">Envoyer</button>
                </div>
            </form>
            <p style="margin-top: 10px;"></p>
            <?=$result?>
        </main>

        <div class="desktop-hide float-item">
            <div class="searchbar student-search">
                <button type="submit" class="submit-search">
                    <i class="fa fa-search"></i>
                </button>
            </div>
        </div>
        
        <div class="" id="site-left">
            <div class="searchbar student-search">
                <label for="search" class="align-center">
                    <form action="" method="get">
                        <input type="text" name="search" id="search" placeholder="Rechercher un etudiant...">
                    </form>
                    <button type="submit" class="submit-search">
                        <i class="fa fa-search"></i>
                    </button>
                </label>
            </div>

            <div>
                <ul>
                    <?php
                        foreach($students as $key => $student) {
                            echo Util::showListStudent($student, (int)$key);
                        }
                    ?>
                </ul>
            </div>
        </div>
    </main>



    </div>
</body>
</html>