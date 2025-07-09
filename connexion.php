<?php

    use App\App;

    require_once "vendor/autoload.php";
    App::start_session();

    if (isset($_SESSION["user"])) {
        header("location:index.php");
    } 

    $app = new App();
    $message = "";

    if (isset($_POST["email"], $_POST["password"])) {
        $email = htmlspecialchars($_POST["email"]) ?? "";
        $password = htmlspecialchars($_POST["password"]) ?? "";

        if (!$message = $app->connect($email, $password)) {
            header("location:index.php");
            exit;
        }
        
    }
?>

<?php
    $page = "connect";
    require_once "components/header.php";

?>

    <main class="site add-student" id="site">
        <div class="title">
            <h1>Se connecter</h1>
        </div>
        
        <form method="POST" action="" class="form flex wrap">
            
            
            <div class="leftpart">
                <div class="form-items flex-column">
                    <table>
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
                                        <label for="password">Code</label>
                                        
                                    </td>
                                    <td>
                                        <label for="password" class="content-input">
                                            <input type="password" name="password" id="password">
                                        </label>
                                    </td>
                                </tr>
                            </div>
                        </table>
                        
                        
                        <button type="submit" class="radius-3 submit" >Se connecter</button>
                    </div>
                </div>
            
        </form>
        <p style="color: red; font-size: 12px;"><?=$message?></p>
    </main>
    
</div>
</body>
</html>