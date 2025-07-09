<?php

    use App\App;
    use App\Utils\Util;

    require_once "vendor/autoload.php";
    App::start_session();

    if(!isset($_SESSION["user"]['manager']) && !$_SESSION["user"]['manager']) {
        header("location: ./index.php");
    }

    $app = new App();
    $offset = 0;
    $count = count($app->get_students());

    if(isset($_GET['offset'])) {
        $offset = $_GET['offset'];
    }

    $students = $app->get_students_offset($offset, 20);
?>


<?php
    $page = "genBordereau";
    require_once "components/header.php";
?>

   <main class="site" id="site">
        <div class="title">
            <h1>Generer des bordereaux</h1>
        </div>

        <div class="filter align-center">
            <label class="content-input">
                <select name="serie" id="">
                    <option value="GL" >GL</option>
                    <option value="SR" selected>SR</option>
                </select>
            </label>
            <label class="content-input">
                <select name="level" id="">
                    <option value="1" selected>1</option>
                    <option value="2">2</option>
                </select>
            </label>
            <label class="content-input">
                <select name="classe" id="">
                    <option value="A" selected>A</option>
                    <option value="A">B</option>
                </select>
            </label>
        </div>

        <div class="table list-table">
            <!-- <caption></caption> -->
            <table>
                <thead>
                    <tr>
                        <td>Nom</td>
                        <td>Filiere</td>
                        <td>Niveau</td>
                        <td>Classe</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach ($students as $key => $student) {
                            echo Util::showStudentBordereau($student, (int)$key, $use);
                        }
                    ?>
                </tbody>
            </table>

            <!-- <div class="pagination" id="pagination">
                <a href="#" class="navigation-button pointer">
                    <button type="button" class="button pointer">
                        <i class="fa fa-arrow-left"></i>
                        Precedent
                    </button>
                </a>
                <a href="#" class="navigation-button pointer">
                    <button type="button" class="button pointer">
                        Suivant
                        <i class="fa fa-arrow-right"></i>
                    </button>
                </a>
            </div> -->
        </div>
    </main>

    </div>
</body>
</html>