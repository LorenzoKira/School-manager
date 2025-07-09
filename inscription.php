<?php

    use App\App;

    require_once "vendor/autoload.php";
    App::start_session();

    if (isset($_SESSION["user"])) {
        header("location:index.php");
    } 

    $app = new App();
    $message = "";

    if (isset($_POST["firstname"], $_POST["lastname"], $_POST["email"], $_POST["tel"], $_POST["place"], $_POST["birth"], $_POST["password"], $_POST["confirm"])) {
        $data = [];

        foreach($_POST as $key => $value) {
            $data[$key] = htmlspecialchars($value);
        }

        $message = $app->register($data);
        if (isset($_SESSION["user"]) && !empty($_SESSION["user"])) {
            header("location: index.php");
        }
    }

    $page = "register";
    require_once "components/header.php";


?>

    <main class="site add-student" id="site">
        <div class="title">
            <h1>S'inscrire</h1>
        </div>
        
        <form action="" method="post" class="form flex wrap ">
            
            
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
                                    <label for="email">Email</label>
                                </td>
                                <td>
                                    <label for="email" class="content-input">
                                        <input type="email" name="email" id="email" placeholder="johndoe@gmail.com">
                                    </label>
                                </td>
                            </tr>
                        </div>
                        <div class="form-item flex">
                            <tr>
                                <td>
                                    <label for="tel">Telephone</label>
                                </td>
                                <td>
                                    <label for="tel" class="content-input">
                                        <input type="text" name="tel" id="tel" placeholder="6XXXXXXXX">
                                    </label>
                                </td>
                            </tr>
                        </div>
                        <div class="form-item flex">
                            <tr>
                                <td>
                                    <label for="place">Lieu Naissance</label>
                                </td>
                                <td>
                                    <label for="place" class="content-input">

                                        <select name="place" id="place">
                                            <option value="Douala">Douala</option>
                                            <option value="Yaounde">Yaounde</option>
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
                                    <label for="birth">Naissance</label>
                                    
                                </td>
                                <td>
                                    <label for="matricule" class="content-input">
                                        <input type="date" name="birth" id="birth">
                                    </label>
                                </td>
                            </tr>
                        </div>
                        <div class="form-item flex">
                            <tr>
                                <td>
                                    <label for="password">Code</label>
                                </td>
                                <td>
                                    <label for="password" class="content-input">
                                        <input type="password" name="password" id="password">
                                    </label>
                                </td>
                            </tr>
                        </div>
                        <div class="form-item flex">
                            <tr>
                                <td>
                                    <label for="confirm">Confirmation</label>
                                </td>
                                <td>
                                    <label for="confirm" class="content-input">
                                        <input type="password" name="confirm" id="confirm">
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