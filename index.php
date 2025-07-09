<?php
    use App\App;
    use App\Utils\Util;

    require_once "vendor/autoload.php";
    App::start_session();

    if(isset($_SESSION["user"])) {
        
    }
?>

<?php
    $page = "index";
    require_once "components/header.php";
?>

    <main class="site" id="site">
        <div class="title">
            <h1>Bienvenue J. Code</h1>
        </div>

        <div class="boxes align-center">
            <div class="box radius-6"></div>
            <div class="box radius-6"></div>
        </div>

        <div class="table">
            <caption>Nos meilleurs etudiants</caption>
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
                    <tr>
                        <td>John Doe</td>
                        <td>SR</td>
                        <td>1</td>
                        <td>A</td>
                        <td class="viewer">
                            <a href="#">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <tr class="gray">
                        <td>John Doe</td>
                        <td>SR</td>
                        <td>1</td>
                        <td>A</td>
                        <td class="viewer">
                            <a href="#">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <tr class="">
                        <td>John Doe</td>
                        <td>SR</td>
                        <td>1</td>
                        <td>A</td>
                        <td class="viewer">
                            <a href="#">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <tr class="gray">
                        <td>John Doe</td>
                        <td>SR</td>
                        <td>1</td>
                        <td>A</td>
                        <td class="viewer">
                            <a href="#">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <tr class="">
                        <td>John Doe</td>
                        <td>SR</td>
                        <td>1</td>
                        <td>A</td>
                        <td class="viewer">
                            <a href="#">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <tr class="gray">
                        <td>John Doe</td>
                        <td>SR</td>
                        <td>1</td>
                        <td>A</td>
                        <td class="viewer">
                            <a href="#">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    </div>
</body>
</html>