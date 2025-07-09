<?php
    namespace App;

    
    use App\Utils\Util;
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    
    use \PDO;
    use \PDOException;
    use \PDOStatement;
    use \stdClass;
    use \DateTime;
    
    require_once "vendor/autoload.php";

?>
<?php 

    final class App 
    {
        
        private $pdo;
        private $mail;
        private static $OFFSET=5;

        public function __construct(){
            try {
                $this->pdo = new PDO("mysql:host=localhost;dbname=notemanager;","root" ,"Sdf123__", [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
                ]);

            } catch (PDOException $e) {
                echo $e->getMessage();
            }


        }

        public static function start_session() {
            if(session_status() != PHP_SESSION_ACTIVE){
                session_start();
            }
        }


        /**
         * Run sql command
         * @param string $sql
         * @param array $params
         * @return bool|PDOStatement
         */
        private function run(string $sql, array $params = []): bool|PDOStatement {
            try {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                return $stmt;
            } catch (PDOException $e) {
                return false;
            }
        }

        /**
         * Returns all names of registered teachers
         * @return array
         */
        public function get_teachers(): array {
            $sql = "SELECT idEnseignant, nom FROM enseignant";
            $stmt = $this->run($sql);
            $data = $stmt->fetchAll();
            return $data;
        }
        public function get_unities(): array {
            $sql = "SELECT idUnite, nomUnite FROM uniteenseignement";
            $stmt = $this->run($sql);
            $data = $stmt->fetchAll();
            return $data;
        }
        public function get_students(): array {
            $sql = "SELECT * FROM etudiant";
            $stmt = $this->run($sql);
            $data = $stmt->fetchAll();
            return $data;
        }
        public function get_students_offset(int $offset = 0, int $limit = 5): array {

            $size = count($this->get_students());

            $sql = "SELECT * FROM etudiant ";

            if($offset > 0) {
                $sql .= "OFFSET $offset";
            }

            if($limit > 0) {
                $sql .= "LIMIT $limit";

            }
            $stmt = $this->run($sql);
            $data = $stmt->fetchAll();
            return $data;
        }

        public function get_student_by_id(int $id): stdClass {
            $sql = "SELECT * FROM etudiant WHERE idEtudiant=:id";
            $stmt = $this->run($sql, [
                ":id" => $id
            ]);
            $data = $stmt->fetch();
            return $data;
        }
        public function get_teacher_by_id(int|null $id): stdClass {
            if(!$id) {
                $sql = "SELECT * FROM manager";
                $stmt = $this->run($sql);
                $data = $stmt->fetch();
            }else{

                $sql = "SELECT * FROM enseignant WHERE idEnseignant=:id";
                $stmt = $this->run($sql, [
                    ":id" => $id
                ]);
                $data = $stmt->fetch();
            }
            return $data;
        }

        public function get_matiere_by_teacher_id(int $teacher_id, bool $isManager): array { 
            $sql = "SELECT * FROM matiere";
            $params = [];
            if(!$isManager){
                $sql .= " WHERE idEnseignant=:id";
                $params = [
                    "id" => $teacher_id
                ];
            }
            $stmt = $this->run($sql, $params);
            $data = $stmt->fetchAll();
            return $data;
        }


        
        public function get_note_by_student_id(int $student_id, int $matiere_id): stdClass | bool{
            $sql = "SELECT * FROM note WHERE idEtudiant=:idStudent AND idMatiere=:idMatiere";
            $stmt = $this->run($sql, [
                ":idStudent" => $student_id,
                ":idMatiere" => $matiere_id
            ]);
            $data = $stmt->fetch();
            return $data;
        }


        /**
         * Connect user to application
         * @param string $email
         * @param string $password
         * @return bool|stdClass
         */
        public function connect(string $email, string $password): bool|stdClass|string {
            $sql = "SELECT * FROM enseignant WHERE email=:mail";
            $stmt = $this->run($sql, [
                ":mail" => $email
            ]);


            $data = $stmt->fetch();

            if($stmt->rowCount() <= 0) {
                return "L'utilisateur et ou le mot de passe n'existe pas";
            }
            
            $isConnect = password_verify($password, $data->motDePasse);
            if(!$isConnect) {
                return "L'utilisateur et ou le mot de passe n'existe pas";
            }
            
            if($stmt->rowCount() > 0 && $isConnect){
                self::start_session();

                $sql = "INSERT INTO emailverify VALUES(null, :grade, :email, :pass, :tel, :firstname, :lastname, :birth, :place, :date, :otp)";
                $date = date("Y-m-d H:i:s", time() + 60 * 10 );
                $otp = Util::genOtp();


                $stmt = $this->run($sql, [
                    ":grade" => 1,
                    ":email" => $data->email,
                    ":pass" => password_hash($data->motDePasse, PASSWORD_DEFAULT),
                    ":tel" => $data->telephone,
                    ":firstname" => $data->nom,
                    ":lastname" => $data->prenom,
                    ":birth" => $data->dateNaissance,
                    ":place" => $data->lieuNaissance,
                    ":date" => $date,
                    ":otp" => password_hash($otp, PASSWORD_DEFAULT)
                ]);

                if($stmt) {
                    $_SESSION["otp"] = [
                        "id"=> $data->idEnseignant,
                        "email" => $data->email,
                        "otp" => $otp,
                        "expire" => $date,
                        "mode" => "connect"
                    ];

                    $this->mail_sending("kiralorenzo001@gmail.com", $data->email, $otp);
                    header("location: ./confirm.php");
                    exit;

                }
            }

            return $stmt->rowCount() > 0 ? $data : false;
        }
        public function confirm_connect(int $id, string $otp, string $email): bool|string {
            $sql = "SELECT * FROM emailverify WHERE email=:mail";
            $stmt = $this->run($sql, [
                ":mail" => $email
            ]);

            $data = $stmt->fetch();
            if(!password_verify($otp, $data->otp)) {
                return "Code OTP incorrect, veuillez reessayer";
            }

            // Ici on a le bon OTP, verifions si il a deja expire
            $expirationDate = new DateTime($data->expireAt);
            $currentTime = new DateTime();
            $currentTime->setTimestamp(time());

            $interval = $currentTime->diff($expirationDate);
            if ($interval->invert > 0) {
                return "Le code a expire";
            }

            // Ici on a un bon code au bon moment

            
            self::start_session();
            $_SESSION["user"] = [
                "id"=> $id,
                "manager"=> false
            ];


            // Apres connexion, on supprime les donnees dans la table temporaire
            $sql = "DELETE FROM emailverify WHERE idEnseignant=:id";

            $stmt = $this->run($sql, [
                ":id" => $data->idEnseignant
            ]);

            

            // Verification s'il s'agit du manager
            $sql = "SELECT * FROM manager WHERE idEnseignant=:id";
            $stmt = $this->run($sql, [
                ":id" => $id
            ]);
            $data = $stmt->fetchAll();
            if($stmt->rowCount() > 0) {
                $_SESSION["user"]["manager"] = true;
            }

            return isset($_SESSION["user"]);
        }

        public function register(array $data): bool|string {
            $sql = "SELECT * FROM enseignant WHERE email=:mail";
            $stmt = $this->run($sql, [
                ":mail" => $data["email"],
            ]);

            $rows = $stmt->fetch();
            if($stmt->rowCount() > 0){
                 // On a un utilisateur utilisant deja cet email
                return "Adresse email deja utilise";
            }

            // Ici on une adresse valide

            if($data["confirm"] != $data["password"]) {
                return "Retaper le meme mot de passe";
            }
            if(strlen($data["password"]) < 8) {
                return "Taille du mot de passe insuffisante, requis : 8 actuel ". strlen($data['password']);
            }


            $sql = "INSERT INTO emailverify VALUES(null, :grade, :email, :pass, :tel, :firstname, :lastname, :birth, :place, :date, :otp)";
            $date = date("Y-m-d H:i:s", time() + 60 * 10 );
            $otp = Util::genOtp();


            $stmt = $this->run($sql, [
                ":grade" => 1,
                ":email" => $data["email"],
                ":pass" => password_hash($data["password"], PASSWORD_DEFAULT),
                ":tel" => $data["tel"],
                ":firstname" => $data["firstname"],
                ":lastname" => $data["lastname"],
                ":birth" => $data["birth"],
                ":place" => $data["place"],
                ":date" => $date,
                ":otp" => password_hash($otp, PASSWORD_DEFAULT)
            ]);

            self::start_session();
            if($stmt){
                $_SESSION["otp"] = [
                    "id"=> $this->pdo->lastInsertId(),
                    "email" => $data["email"],
                    "otp" => $otp,
                    "expire" => $date,
                    "mode" => "register"
                ];
                
                $this->mail_sending("kiralorenzo001@gmail.com", $data["email"], $otp);

                header("location: ./confirm.php");
                exit;
            }else {
                return "Veuillez remplir tous les champs";
            }
        }

        public function confirm_register(int $id, string $otp, string $email): bool|string {
            $sql = "SELECT * FROM emailverify WHERE email=:mail";
            $stmt = $this->run($sql, [
                ":mail" => $email
            ]);

            $data = $stmt->fetch();
            if(!password_verify($otp, $data->otp)) {
                return "Code OTP incorrect, veuillez reessayer";
            }

            // Ici on a le bon OTP, verifions si il a deja expire
            $expirationDate = new DateTime($data->expireAt);
            $currentTime = new DateTime();
            $currentTime->setTimestamp(time());

            $interval = $currentTime->diff($expirationDate);
            if ($interval->invert > 0) {
                return "Le code a expire";
            }

            // Ici on a un bon code au bon moment

            $sql = "INSERT INTO enseignant VALUES(null, :grade, :email, :pass, :tel, :firstname, :lastname, :birth, :place)";

            $stmt = $this->run($sql, [
                ":grade" => 1,
                ":email" => $data->email,
                ":pass" => password_hash($data->motDePasse, PASSWORD_DEFAULT),
                ":tel" => $data->telephone,
                ":firstname" => $data->nom,
                ":lastname" => $data->prenom,
                ":birth" => $data->dateNaissance,
                ":place" => $data->lieuNaissance
            ]);

            self::start_session();
            if($stmt){
                $_SESSION["user"] = [
                    "id"=> $this->pdo->lastInsertId()
                ];



                // Apres insertion on supprime les donnees dans la table temporaire
                $sql = "DELETE FROM emailverify WHERE idEnseignant=:id";

                $stmt = $this->run($sql, [
                    ":id" => $data->idEnseignant
                ]);
            }else {
                return "Veuillez remplir tous les champs";
            }

            return isset($_SESSION["user"]);
        }

        public function update_otp(int $which, string $value): void {
            $sql = "UPDATE emailverify set otp=:otp, expireAt=:expire WHERE idEnseignant=:id";
            $expirationDate = date("Y-m-d H:i:s", time() + 60 * 10);

            $stmt = $this->run($sql, [
                ":otp" => password_hash($value, PASSWORD_DEFAULT),
                ":expire" => $expirationDate,
                ":id" => (int)$which
            ]);

            if($stmt) {
                self::start_session();
                $_SESSION["otp"]["otp"] = $value;
                $_SESSION["otp"]["expire"] = $expirationDate;
                $this->mail_sending("kiralorenzo001@gmail.com", $_SESSION["otp"]["email"], $_SESSION["otp"]["otp"]);
            }
        }

        private function mail_sending(string $source, string $target, string $subject): void {

            $this->mail = new PHPMailer(true);
            try {
                // Paramètres SMTP
                $this->mail->isSMTP();
                $this->mail->Host       = 'smtp.gmail.com';
                $this->mail->SMTPAuth   = true;
                $this->mail->Username   = $source;                  // Ton adresse Gmail
                $this->mail->Password   = 'jfdykgnqjlviglke';      // Le mot de passe généré par Google
                $this->mail->SMTPSecure = 'ssl';                  // ou 'tls'
                $this->mail->Port       = 465;                   // SSL : 465, TLS : 587

                                                               // Expéditeur / Destinataire
                $this->mail->setFrom($source, 'NoteManager');
                $this->mail->addAddress($target);

                // Contenu
                $this->mail->isHTML(true);
                $this->mail->Subject = 'Code de verification NoteManager';
                $this->mail->Body    = $subject;
                $this->mail->AltBody = $subject;

                $this->mail->send();
                echo 'Message envoyé avec succès !';
            } catch (Exception $e) {
                echo "Erreur : {$this->mail->ErrorInfo}";
            }
        }

        public function add_matiere(array $data): string {
            $sql = "SELECT * FROM matiere WHERE nomMatiere=:name";
            $stmt = $this->run($sql, [
                ":name" => $data["name"]
            ]);

            if($stmt->rowCount() > 0){
                 // La matiere est deja enregistree
                return "La matière exite déjà";
            }

            // On n'a pas encore la matiere dans la BD
            $sql = "INSERT INTO matiere VALUES(NULL, :unite, :name, :coef, :respo, :level, :semestre)";
            $stmt = $this->run($sql, [
                ":unite" => $data["unity"],
                ":name"  => $data["name"],
                ":coef"  => $data["coef"],
                ":respo" => $data["respo"],
                ":level" => $data['level'],
                ":semestre" => $data['semestre']
            ]);

            if(!$stmt){
                return "Remplissez tous les champs";
            }

            return "";
        }
        public function add_unite(array $data): string {
            $sql = "SELECT * FROM uniteenseignement WHERE nomUnite=:name";
            $stmt = $this->run($sql, [
                ":name" => $data["name"]
            ]);

            if($stmt->rowCount() > 0){
                 // L'unite est deja enregistree
                return "L'unité d'enseignement exite déjà";
            }

            // On n'a pas encore l'unite dans la BD
            $sql = "INSERT INTO uniteenseignement VALUES(NULL, :name, :credit)";
            $stmt = $this->run($sql, [
                ":name"  => $data["name"],
                ":credit" => (int)$data["credit"]
            ]);

            if(!$stmt){
                return "Remplissez tous les champs";
            }

            return "";
        }
        public function add_student(array $data): string {
            // On peut avoir des etudiant avec des memes informations (PRESQUE)

            $sql = "INSERT INTO etudiant VALUES(NULL, :level, :filiere, :class, :name, :surname, :date, :place)";
            $stmt = $this->run($sql, [
                ":level"   => $data["level"],
                ":filiere" => $data["serie"],
                ":class"   => $data["class"],
                ":name"    => $data["name"],
                ":surname" => $data["surname"],
                ":date"    => $data["birth"],
                ":place"   => $data["place"]
            ]);

            if(!$stmt){
                return "Remplissez tous les champs";
            }

            return "";
        }

        public function add_notes(array $data, bool $manager=false): true | string{
            $unsaved = [];
            $allPass = true;
            $message = "";

            foreach ($data as $key => $value) {

                if($value['note'] < 0 || $value['note'] > 20){
                    $message = "La note de $key doit être comprise entre 0 et 20 inclus";
                    $allPass = false;
                    continue;
                }

                $sql = "SELECT * FROM note WHERE idMatiere=:idMatiere AND idEtudiant=:idStudent";
                $stmt = $this->run($sql, [
                    ":idMatiere" => $value["idMatiere"],
                    ":idStudent" => $value["id"]
                ]);
                $noteInfo = $stmt->fetch();
                

                if($stmt->rowCount() > 0){
                    // La note de la matiere est deja enregistree
                    $unsaved = array_merge($unsaved, [$key]);
                    $allPass = false;
                    if($manager) {
                        $sql = "UPDATE note set note=:note WHERE idNote=:idNote";
                        $stmt = $this->run($sql, [
                            ":note" => $value["note"],
                            ":idNote" => $noteInfo->idNote
                        ]);
                    }
                    else {
                        if(!$noteInfo->idEnseignant) {
                            $sql = "UPDATE note set note=:note, idEnseignant=:idTeacher WHERE idNote=:idNote";
                            $stmt = $this->run($sql, [
                                ":note" => $value["note"],
                                ":idNote" => $noteInfo->idNote,
                                ":idTeacher" => $value["idTeacher"]
                            ]);
                        }
                    }
                }
                else {

                        $sql = "INSERT INTO note VALUES(NULL, :idMatiere, :note, :idStudent, NULL)";
                        $params = [
                        ":idMatiere" => $value["idMatiere"],
                        ":note" => $value["note"],
                        ":idStudent" => $value["id"],
                    ];
                    if(!$manager) {
                        $sql = "INSERT INTO note VALUES(NULL, :idMatiere, :note, :idStudent, :idTeacher)";
                        $params = array_merge($params, [":idTeacher" => $value["idTeacher"]]);
                    }
                    $stmt = $this->run($sql, $params);
                    
                    if(!$stmt) {
                        $allPass = false;
                    }
                    
                }
                
            }

            $unsaved = implode(", ", $unsaved);
            return !$allPass ? "<p style='color: blue; font-size: 12px;'>Notes de $unsaved Reenregistées, $message</p>" : "<p style='color: green; font-size: 12px;'>Ajout effectué avec succès</p>";
        }

        public function get_effectif(string $filiere, string $classe, int $niveau): int {

            $sql = "SELECT count(*) as count FROM etudiant WHERE filiere=:filiere AND classe=:classe AND niveau=:niveau";
            $stmt = $this->run($sql, [
                ":filiere" => $filiere,
                ":classe" => $classe,
                ":niveau" => $niveau
            ]);

            return $stmt->fetch()->count;
        }

        public function get_infos(int $id) : array {

            $sql = "SELECT matiere.nomMatiere, matiere.coefficient, note.note, matiere.coefficient * note.note AS note_coeff, note.idEnseignant, matiere.idMatiere
                    FROM note
                    INNER JOIN matiere ON note.idMatiere=matiere.idMatiere
                    WHERE idEtudiant=:id";  

            $stmt = $this->run($sql, [
                ":id" => $id
            ]);

            $data = $stmt->fetchAll();
            return $data;
        }
        public function get_infos_unite(int $id) : array {

            $sql = "SELECT matiere.nomMatiere, matiere.coefficient, note.note, matiere.coefficient * note.note AS note_coeff, note.idEnseignant, matiere.idMatiere, matiere.idUnite, uniteenseignement.nomUnite, uniteenseignement.credit
                    FROM note
                    INNER JOIN matiere ON note.idMatiere=matiere.idMatiere
                    INNER JOIN uniteenseignement ON matiere.idUnite=uniteenseignement.idUnite
                    WHERE idEtudiant=:id 
                    ORDER BY idUnite ASC";  

            $stmt = $this->run($sql, [
                ":id" => $id
            ]);

            $data = $stmt->fetchAll();
            return $data;
        }
        public function get_note_from_classe_matiere(string $filiere, string $classe, int $niveau, int $matiere): array {
            $sql = "SELECT note.note, note.idMatiere, note.idEtudiant FROM note
                    INNER JOIN etudiant ON
                    note.idEtudiant = etudiant.idEtudiant
                    WHERE etudiant.filiere = :filiere AND etudiant.classe = :classe AND etudiant.niveau = :niveau AND note.idMatiere=:idMatiere
                    ORDER BY note.note DESC";
                    
            $stmt = $this->run($sql, [
                ":filiere" => $filiere,
                ":classe" => $classe,
                ":niveau" => $niveau,
                ":idMatiere" => $matiere
            ]);

            return $stmt->fetchAll();
        }
        public function get_note_from_classe(string $filiere, string $classe, int $niveau): array {
            $sql = "SELECT note.note * matiere.coefficient as note, note.idMatiere, note.idEtudiant FROM note
                    INNER JOIN etudiant ON
                    note.idEtudiant = etudiant.idEtudiant
                    INNER JOIN matiere ON
                    note.idMatiere = matiere.idMatiere
                    WHERE etudiant.filiere = :filiere AND etudiant.classe = :classe AND etudiant.niveau = :niveau
                    ORDER BY note.note DESC";
                    
            $stmt = $this->run($sql, [
                ":filiere" => $filiere,
                ":classe" => $classe,
                ":niveau" => $niveau
            ]);

            return $stmt->fetchAll();
        }

        public function get_id(string $filiere, string $classe, int $niveau) {
            $sql = "SELECT DISTINCT note.idEtudiant FROM note
            INNER JOIN etudiant
            ON note.idEtudiant=etudiant.idEtudiant
            WHERE etudiant.filiere=:filiere AND etudiant.classe=:classe AND etudiant.niveau=:niveau";


            $stmt = $this->run($sql, [
                ":filiere" => $filiere,
                ":classe" => $classe,
                ":niveau" => $niveau
            ]);

            return $stmt->fetchAll();
        }

        public function get_note_unite(string $filiere, string $classe, int $niveau) {
            $sql = "SELECT note.note * matiere.coefficient as note_coeff, note.note, note.idMatiere, note.idEtudiant, matiere.idUnite FROM note
            INNER JOIN etudiant ON
            note.idEtudiant = etudiant.idEtudiant
            INNER JOIN matiere ON
            note.idMatiere = matiere.idMatiere
            WHERE etudiant.filiere = :filiere AND etudiant.classe = :classe AND etudiant.niveau = :niveau
            ORDER BY idUnite ASC";

            $stmt = $this->run($sql, [
                ":filiere" => $filiere,
                ":classe" => $classe,
                ":niveau" => $niveau
            ]);
            
            return $stmt->fetchAll();
        }

    }
    


    