<?php 
    namespace App\Utils;

use App\App;
use stdClass;

    class Util {

        public static function genOtp (int $length = 6) {
            return str_pad((string)random_int(0, pow(10, $length)), $length, 0, STR_PAD_LEFT);
        }

        public static function genOptionTeacher(stdClass $data) {
            return <<<html
            <option value="$data->idEnseignant">$data->nom</option>
html;
        }

        public static function genOptionUnity(stdClass $data) {
            return <<<html
            <option value="$data->idUnite">$data->nomUnite</option>
html;
        }
        public static function showListStudent(stdClass $data, int $idx) {
            $class = $idx % 2 != 0 ?: "gray";
            return <<<html
            <a href="ajouter_note.php?id=$data->idEtudiant" class="$class">
                <li>
                    <span>$data->nom $data->prenom</span>
                    <span>$data->filiere$data->niveau$data->classe</span>
                </li>
            </a>
html;
        }

        public static function isEmpty(...$items): bool {
            $count = array_reduce($items, fn($carry, $item)=> empty($item) ? $carry + 1 : $carry, 0);
            return $count !== 0;
        }

        public static function showMatiereByTeacher(stdClass $data, int $note) {
            $note = $note < 0 ? "" : $note;
            return <<<html
                <div class="form-item flex">
                    <tr>
                        <td>
                            <label for="$data->nomMatiere">$data->nomMatiere</label>
                            
                        </td>
                        <td>
                            <label for="$data->nomMatiere" class="content-input">
                                <input type="number" name="$data->nomMatiere" id="$data->nomMatiere" value="$note">
                            </label>
                        </td>
                    </tr>
                </div>
html;

        }

        public static function showStudent(stdClass $data, int $idx) {
            $class = $idx % 2 != 0 ?: "gray";
            return <<<html
                <tr class="$class">
                    <td>$data->nom</td>
                    <td>$data->filiere</td>
                    <td>$data->niveau</td>
                    <td>$data->classe</td>
                    <td class="viewer">
                        <a href="./ajouter_note.php?id=$data->idEtudiant">
                            <i class="fa fa-eye"></i>
                        </a>
                    </td>
                </tr>
html;
        }
        public static function showStudentBordereau(stdClass $data, int $idx, int $use) {
            $class = $idx % 2 != 0 ?: "gray";
            $file = $use == 1 ? "bordereau_unite.php" : "bordereau_resultats.php";
            return <<<html
                <tr class="$class">
                    <td>$data->nom</td>
                    <td>$data->filiere</td>
                    <td>$data->niveau</td>
                    <td>$data->classe</td>
                    <td class="viewer">
                        <a href="./$file?id=$data->idEtudiant">
                            <i class="fa fa-file"></i>
                        </a>
                    </td>
                </tr>
html;
        }

        public static function showInfo(stdClass $data, int $rank): string {
            $note_coeff = $data->note * $data->coefficient;
            $app = new App();
            $teacher = $app->get_teacher_by_id($data->idEnseignant);
            $appreciation = self::getAppreciation($data->note);

            return <<<html
                    <tr>
                        <td class="subject">$data->nomMatiere</td>
                        <td>$data->coefficient</td>
                        <td>$data->note</td>
                        <td>$note_coeff</td>
                        <td>$rank</td>
                        <td>$appreciation</td>
                        <td>$teacher->nom $teacher->prenom</td>
                    </tr>
html;
        }

        public static function getAppreciation(float $nb): string {

            if($nb < 10) {
                return "INSUFFISANT";
            } else if($nb < 12) {
                return "PASSABLE";
            } else if($nb < 14) {
                return "ASSEZ BIEN";
            } else if($nb < 16) {
                return "BIEN";
            } else if($nb < 18) {
                return "TRES BIEN";
            } else if($nb < 20) {
                return "EXCELLENT";
            } else {
                return "PARFAIT";
            }
        }

        public static function format_number(float $nb): string {

            return number_format(num: $nb,decimals: 2,decimal_separator: ".",thousands_separator: "");
        }

        public static function findRank(array $data, int $searched): float|int {
            $position = 1;

            foreach($data as $key => $value) {
                if($value->idEtudiant == $searched) {
                    $position = $key + 1;
                }
            }

            return $position;
        }

        public static function dump(mixed $items): void {
            echo "<pre>";
            var_dump($items);
            echo "</pre>";
        }

        public static function findRankInArray(array $array, int $searched): float|int {
            foreach ($array as $key => $value) {
                if($value[1] == $searched) {
                    return $key + 1;
                }
            }
            return -1;
        }

        public static function sampleArray(array $data): array {
            $new = [];
            $current = -1;
            foreach ($data as $info) {
                if($current != $info->idUnite) {
                    $current = $info->idUnite;
                    $new[$current] = [$info];
                } else {
                    array_push($new[$current], $info);
                }
            }

            return $new;
        }

        public static function showInfoUnite(stdClass $data, int|null $rows, int $rank, string | null $means = null): string {
            $app = new App();
            $teacher = $app->get_teacher_by_id($data->idEnseignant);
            $appreciation = self::getAppreciation($data->note);
            $unite_rows = "";
            $moyenne_rows = "";
            $credit_rows = "";

            if($rows) {
                $unite_rows = "<td rowspan=$rows>$data->nomUnite</td>";
                $moyenne_rows = "<td rowspan=$rows>$means</td>";
                $credit_rows = "<td rowspan=$rows>$data->credit</td>";
            }

            return <<<html
                    <tr>
                        $unite_rows
                        <td class="subject">$data->nomMatiere</td>
                        <td>$data->coefficient</td>
                        <td>$data->note</td>
                        <td>$data->note_coeff</td>
                        $moyenne_rows
                        <td>$rank</td>
                        <td>$appreciation</td>
                        <td>$teacher->nom $teacher->prenom</td>
                        $credit_rows
                    </tr>
html;
        }

        public static function mean_by_unity(array $data): array {
            $current = -1;
            $sum = 0;
            $means = [];
            foreach ($data as $keys => $values) {
                foreach ($values as $key => $value) {
                    $sum += $value->note;
                }
                if($current != $keys) {
                    $current = $keys;
                    $means[$current] = Util::format_number($sum / count($values));
                    $sum = 0;

                }
            }

            return $means;
        }


    }

