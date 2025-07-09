<?php

    use App\App;
    use App\Utils\Util;
use Spatie\Browsershot\Browsershot;

    require_once "vendor/autoload.php";
    require_once "vendor/mpdf/mpdf/mpdf.php";
    App::start_session();

    if(!isset($_SESSION["user"]["manager"]) || !$_SESSION["user"]["manager"]) {
        header("location: ./index.php");
    }

    $app = new App();
    $message = "";
    $id = (int)htmlspecialchars($_GET["id"]);

    $student = $app->get_student_by_id($id);
    $effectif = $app->get_effectif($student->filiere, $student->classe, $student->niveau);
    $date = new DateTime($student->dateNaissance);
    $today = new DateTime("now");
    $MAX = 20;

    $infos = $app->get_infos($id);
    $notes = $app->get_note_from_classe($student->filiere, $student->classe, $student->niveau);
    $ids = $app->get_id($student->filiere, $student->classe, $student->niveau);


    $matiere = 1;
    $totaux = [];
    $ranks = [];
    $i = 0;
    $total_by_student = [];
    foreach ($ids as $id) {
        $id = $id->idEtudiant;
        $total = 0;
        foreach ($notes as $note_student) {
            if ($note_student->idEtudiant == $id) {
                $total += $note_student->note;
            }
        }

        array_push($total_by_student, [$total, $id]);
    }

    // Sort array
    
    array_multisort($total_by_student, SORT_DESC);
    $final_rank = Util::findRankInArray($total_by_student, $student->idEtudiant);
    

    $totaux = [
        "note" => array_reduce(array_map(fn($info) => $info->note,
         $infos),
          fn($carry, $item) => $carry + $item, 
          0),
        "coef" => array_reduce(array_map(fn($info) => $info->coefficient,
         $infos),
          fn($carry, $item) => $carry + $item, 
          0),
        "note_coef" => array_reduce(array_map(fn($info) => $info->coefficient * $info->note,
         $infos),
          fn($carry, $item) => $carry + $item, 
          0),
    ];

    $page = "";
?>

<!DOCTYPE html>
<html lang='FR-fr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Bordereau de Résultats</title>
    </head>
    <style>
        @page {
            margin: 2cm;
            size: A4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: white;
        }

        .document {
            max-width: 21cm;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border: 1px solid #000;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 14px;
            font-weight: normal;
        }

        .school-info {
            text-align: center;
            margin-bottom: 25px;
            font-size: 11px;
        }

        .student-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            border: 1px solid #000;
            padding: 15px;
        }

        .student-info .left,
        .student-info .right {
            flex: 1;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
        }

        .info-row .label {
            font-weight: bold;
            width: 120px;
        }

        .info-row .value {
            flex: 1;
            border-bottom: 1px dotted #666;
            padding-left: 10px;
        }

        .results-section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-size: 11px;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }

        td.subject {
            text-align: left;
            padding-left: 10px;
        }

        .summary-section {
            border: 2px solid #000;
            padding: 15px;
            margin-bottom: 25px;
        }

        .summary-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .summary-content {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        .average-box {
            text-align: center;
            border: 1px solid #000;
            padding: 15px;
            min-width: 120px;
        }

        .average-label {
            font-size: 10px;
            margin-bottom: 5px;
        }

        .average-value {
            font-size: 20px;
            font-weight: bold;
        }

        .mention-box {
            text-align: center;
            border: 1px solid #000;
            padding: 15px;
            min-width: 150px;
        }

        .mention-label {
            font-size: 10px;
            margin-bottom: 5px;
        }

        .mention-value {
            font-size: 16px;
            font-weight: bold;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-block {
            text-align: center;
            width: 200px;
        }

        .signature-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 50px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .signature-date {
            font-size: 10px;
            margin-top: 10px;
        }

        .generate {
            position: fixed;
            padding: 7px 10px;
            background-color: #5761F3;
            color: #fff;
            border: none;
            border-radius: 3px;
            top: 15px;
            right: 15px;
            cursor: pointer;
            font-family: "Poppins", sans-serif;

        }

        @media print {
            body {
                font-size: 11px;
            }
            
            .document {
                margin: 0;
                padding: 0;
            }
            
            .signatures {
                page-break-inside: avoid;
            }
        }
    </style>
    <body>
    <?php

    $page .= "
    <!DOCTYPE html>
    <html lang='FR-fr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Bordereau de Résultats</title>
    </head>
<body>
    <div class='document'>
                    <div class='header'>
                        <h1>République du Cameroun</h1>
                        <div class='subtitle'>Ministère de l'Éducation Nationale</div>
                    </div>

                    <div class='school-info'>
                        <strong>CENTRE D'EXCELLENCE DOT SCHOOL</strong><br>
                        B.P. 1234 - Douala, Cameroun<br>
                        Tél: +237 233 33 55 66 - Email: contact@dotschool.cm
                    </div>

                    <div class='header'>
                        <h1>Bordereau de Résultats</h1>
                        <div class='subtitle'>Année Scolaire 2024-2025 - Fin d'année</div>
                    </div>

                    <div class='student-info'>
                        <div class='left'>
                            <div class='info-row'>
                                <span class='label'>Nom et Prénom:</span>
                                <span class='value'> " . strtoupper("$student->nom $student->prenom") . "</span>
                            </div>
                            <div class='info-row'>
                                <span class='label'>Identifiant:</span>
                                <span class='value'>" . str_pad($student->idEtudiant, 6, 0, STR_PAD_LEFT) . "</span>
                            </div>
                            <div class='info-row'>
                                <span class='label'>Date de naissance:</span>
                                <span class='value'> " . $date->format('d M Y') . "</span>
                            </div>
                            <div class='info-row'>
                                <span class='label'>Lieu de naissance:</span>
                                <span class='value'>$student->lieuNaissance</span>
                            </div>
                        </div>
                        <div class='right'>
                            <div class='info-row'>
                                <span class='label'>Classe:</span>
                                <span class='value'>$student->filiere$student->classe$student->niveau</span>
                            </div>
                            <div class='info-row'>
                                <span class='label'>Effectif:</span>
                                <span class='value'>$effectif</span>
                            </div>
                            <div class='info-row'>
                                <span class='label'>Date d'édition:</span>
                                <span class='value'> " . $today->format('d M Y') . "</span>
                            </div>
                        </div>
                    </div>

                    <div class='results-section'>
                        <div class='section-title'>Détail des Notes par Matière</div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Matières</th>
                                    <th>Coeff.</th>
                                    <th>Note/20</th>
                                    <th>Note × Coeff.</th>
                                    <th>Rang</th>
                                    <th>Appréciation</th>
                                    <th>Nom du Professeur</th>
                                </tr>
                            </thead>
                            <tbody>";
        foreach($infos as $info) {
            $all_note_class = $app->get_note_from_classe_matiere($student->filiere, $student->classe, $student->niveau, $info->idMatiere);
            // Find rank
            $rank = Util::findRank($all_note_class, $student->idEtudiant);
            array_push($ranks, $rank);
            $page .= Util::showInfo($info, $rank);
        }

        $page .= "<tr style='font-weight: bold; background-color: #f0f0f0;'>
                <td class='subject'>TOTAUX</td>
                <td>" . $totaux['coef'] . "</td>
                <td>" . $totaux['note'] . " </td>
                <td>" . $totaux['note_coef'] . "</td>
                <td>$final_rank</td>
                <td>-</td>
                <td>-</td>
            </tr>
                    
            </tbody>
            </table>
        </div>

        <div class='summary-section'>
            <div class='summary-title'>Résultats Généraux</div>
            <div class='summary-content'>
                <div class='average-box'>
                    <div class='average-label'>MOYENNE</div>
                    <div class='average-value'> " . Util::format_number($totaux['note_coef'] / $totaux['coef']) ." / " .  $MAX . "</div>
                </div>
                <div class='mention-box'>
                    <div class='mention-label'>MENTION</div>
                    <div class='mention-value'> " . Util::getAppreciation($totaux['note_coef'] / $totaux['coef']) . "</div>
                </div>
                <div class='average-box'>
                    <div class='average-label'>RANG DANS LA CLASSE</div>
                    <div class='average-value'> $final_rank " . ($final_rank == 1 ? 'er' : 'eme') . " / " . $effectif . " </div>
                </div>
            </div>
        </div>

        <div class='signatures'>
            <div class='signature-block'>
                <div class='signature-title'>Le Chef d'Établissement</div>
                <div class='signature-date'>Fait à Douala, le " . $today->format('d M Y') . "</div>
            </div>
            <div class='signature-block'>
                <div class='signature-title'>Le Professeur Principal</div>
                <div class='signature-date'>Visa et Cachet</div>
            </div>
            <div class='signature-block'>
                <div class='signature-title'>L'Élève</div>
                <div class='signature-date'>Signature</div>
            </div>
        </div>
    </div>

    <footer>
    
    </footer>
</body>
</html>";
?>
        
<div class="document">
        <div class="header">
            <h1>République du Cameroun</h1>
            <div class="subtitle">Ministère de l'Éducation Nationale</div>
        </div>

        <div class="school-info">
            <strong>CENTRE D'EXCELLENCE DOT SCHOOL</strong><br>
            B.P. 1234 - Douala, Cameroun<br>
            Tél: +237 233 33 55 66 - Email: contact@dotschool.cm
        </div>

        <div class="header">
            <h1>Bordereau de Résultats</h1>
            <div class="subtitle">Année Scolaire 2024-2025 - Fin d'année</div>
        </div>

        <div class="student-info">
            <div class="left">
                <div class="info-row">
                    <span class="label">Nom et Prénom:</span>
                    <span class="value"><?= strtoupper($student->nom. " " . $student->prenom)?></span>
                </div>
                <div class="info-row">
                    <span class="label">Identifiant:</span>
                    <span class="value"><?= str_pad($student->idEtudiant, 6, 0, STR_PAD_LEFT) ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Date de naissance:</span>
                    <span class="value"><?= $date->format("d M Y") ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Lieu de naissance:</span>
                    <span class="value"><?=$student->lieuNaissance?></span>
                </div>
            </div>
            <div class="right">
                <div class="info-row">
                    <span class="label">Classe:</span>
                    <span class="value"><?= "$student->filiere$student->classe$student->niveau"?></span>
                </div>
                <div class="info-row">
                    <span class="label">Effectif:</span>
                    <span class="value"><?=$effectif?></span>
                </div>
                <!-- <div class="info-row">
                    <span class="label">Rang:</span>
                    <span class="value">1</span>
                </div> -->
                <div class="info-row">
                    <span class="label">Date d'édition:</span>
                    <span class="value"><?=$today->format("d M Y")?></span>
                </div>
            </div>
        </div>

        <div class="results-section">
            <div class="section-title">Détail des Notes par Matière</div>
            <table>
                <thead>
                    <tr>
                        <th>Matières</th>
                        <th>Coeff.</th>
                        <th>Note/20</th>
                        <th>Note × Coeff.</th>
                        <th>Rang</th>
                        <th>Appréciation</th>
                        <th>Nom du Professeur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($infos as $info) {
                            $all_note_class = $app->get_note_from_classe_matiere($student->filiere, $student->classe, $student->niveau, $info->idMatiere);
                            // Find rank
                            $rank = Util::findRank($all_note_class, $student->idEtudiant);
                            array_push($ranks, $rank);

                            echo Util::showInfo($info, $rank);
                        }
                    ?>
                    <tr style="font-weight: bold; background-color: #f0f0f0;">
                        <td class="subject">TOTAUX</td>
                        <td><?=$totaux['coef']?></td>
                        <td><?=$totaux['note']?></td>
                        <td><?=$totaux['note_coef']?></td>
                        <td><?= $final_rank?></td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="summary-section">
            <div class="summary-title">Résultats Généraux</div>
            <div class="summary-content">
                <div class="average-box">
                    <div class="average-label">MOYENNE</div>
                    <div class="average-value"><?= Util::format_number($totaux['note_coef'] / $totaux['coef'])?>/<?=$MAX?></div>
                </div>
                <div class="mention-box">
                    <div class="mention-label">MENTION</div>
                    <div class="mention-value"><?=Util::getAppreciation($totaux['note_coef'] / $totaux['coef'])?></div>
                </div>
                <div class="average-box">
                    <div class="average-label">RANG DANS LA CLASSE</div>
                    <div class="average-value"><?=$final_rank?><?= $final_rank == 1 ? "er" : "eme" ?> / <?=$effectif?></div>
                </div>
            </div>
        </div>

        <div class="signatures">
            <div class="signature-block">
                <div class="signature-title">Le Chef d'Établissement</div>
                <div class="signature-date">Fait à Douala, le <?=$today->format("d M Y")?></div>
            </div>
            <div class="signature-block">
                <div class="signature-title">Le Professeur Principal</div>
                <div class="signature-date">Visa et Cachet</div>
            </div>
            <div class="signature-block">
                <div class="signature-title">L'Élève</div>
                <div class="signature-date">Signature</div>
            </div>
        </div>
    </div>

    <footer>

        <a href="bordereau_resultats.php?id=<?=$id?>&generate=true">
            <button class="generate">
                Generer PDF
            </button>
        </a>
    </footer>

</body>
</html>  

<?php
    if(isset($_GET['generate']) && $_GET['generate'] == 'true') {

    $path = "$student->filiere\\$student->classe\\$student->niveau";
    var_dump($student);

    if(!is_dir($path)) {
        mkdir($path, 0777, true);
    }

    Browsershot::html($page)
        ->setNodeBinary('C:\\Program Files\\nodejs\\node.exe')
        ->setChromePath('C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe')
        ->setOption('format', 'A4')
        ->setOption('landscape', true)
        ->setOption('viewport-width', 1500)
        ->setOption('page-height', 'auto')
        ->save("$path\\$student->nom$student->prenom.pdf");
}
?>