<?php

    use App\App;
    use App\Utils\Util;

    require_once "vendor/autoload.php";
    App::start_session();
    $app = new App();
    $message = "";


    if (isset($_POST["otp"])) {
        $otp = htmlspecialchars($_POST["otp"]) ?? "";



        if($_SESSION["otp"]["mode"] == "connect") {
            if (is_bool($message = $app->confirm_connect($_SESSION["otp"]["id"], $otp, $_SESSION["otp"]["email"]))) {
                echo $_SESSION["user"]["id"];
                var_dump($data);
                header("location:index.php");
            }
        }else {
            if (is_bool($message = $app->confirm_register($_SESSION["otp"]["id"], $otp, $_SESSION["otp"]["email"]))) {
                echo $_SESSION["user"]["id"];
                var_dump($data);
                header("location:index.php");
            }
        }
        
    }

    // Cas de la regeneration de l'OTP
    if(isset($_POST["regenerate"])) {
        $app->update_otp((int)$_SESSION["otp"]["id"], Util::genOtp());
        header("location: ./confirm.php");
        exit();
    }
?>

<?php
    $page = "otp";
    var_dump($_SESSION);
    require_once "components/header.php";

?>

    <main class="site add-student" id="site">
        <div class="title">
            <h1>Confirmation de l'utilisateur</h1>
        </div>
        
        <form method="POST" action="" class="form flex wrap">
            
            
            <div class="leftpart">
                <div class="form-items flex-column">
                    <table>
                        <div class="form-item flex">
                            <p style="position: absolute; bottom: 10px;">verifiez votre boite mail et confirmer le code envoyer a <a href="#"><?=$_SESSION["otp"]["email"]?></a></p>
                            <tr>
                                <td>
                                    <label for="otp">OTP</label>
                                    
                                </td>
                                <td>
                                    <label for="email" class="content-input">
                                        <input type="number" name="otp" id="otp" placeholder="123456" max="999999" min="000000" maxlength="6">
                                    </label>
                                </td>
                            </tr>
                        </div>
                    </table>
                </div>
                <p style="color: red; font-size: 12px;"><?= $message ?? "" ?></p>
            </div>
            <?=
                $message != "Le code a expire"? "<div class='rightpart'>
                <div class='form-items flex-column'>
                    <table>
                        <div class='form-item flex'>
                            <tr>
                                <td>
                                    <button type='submit'>S'incrire</button>
                                </td>
                            </tr>
                        </div>
                    </table>
                </div>
            </div>" : "<div class='rightpart'>
                <div class='form-items flex-column'>
                    <table>
                        <div class='form-item flex'>
                            <tr>
                                <td>
                                    <button type='submit' name='regenerate'>Regenerer</button>
                                </td>
                            </tr>
                        </div>
                    </table>
                </div>
            </div>"
            ?>
            
                <hr class="desktop-hide">
                </div>

        </form>
    </main>

    </div>
</body>
</html>