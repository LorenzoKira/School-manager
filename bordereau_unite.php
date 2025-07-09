<?php

    use App\App;
    use App\Utils\Util;
    use Knp\Snappy\Pdf;
use Spatie\Browsershot\Browsershot;

    require_once "vendor/autoload.php";
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
    $total_by_student = [];
    $MAX = 20;
    
    $infos = $app->get_infos_unite($id);
    // Rearranger le tableau
    $news = Util::sampleArray($infos);
    $mean_by_unity = Util::mean_by_unity($news);

    $notes = $app->get_note_unite($student->filiere, $student->classe, $student->niveau);
    $ids = $app->get_id($student->filiere, $student->classe, $student->niveau);

    
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


    $matiere = 1;
    $totaux = [];
    $ranks = [];
    $credits = array_map(fn($array) => $array[0]->credit, $news);
    $i = 0;
    $total_by_student = [];

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
        "credit" => array_reduce($credits, fn($carry, $item) => $carry + $item, 0),
        "means" => array_reduce($mean_by_unity, fn($carry, $current) => $carry + $current, 0)
    ];


    $page = "";
?>





<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bordereau de Résultats</title>
    <link rel="stylesheet" href="assets/css/all.min.css">
    <script src="assets/app.js" defer></script>
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

        .hide {
            display: none !important;
        }

        .student-info .left,
        .student-info .right {
            flex: 1;
        }

        .flex {
            display: flex;
        }

        .item-center {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .flex.win {
            color: green;
            font-size: 14px;
        }

        .align-center {
            display: flex;
            align-items: center;
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

        footer {
            position: relative;
        }

        .footer {
            position: absolute;
            bottom: 1cm;
            left: 2cm;
            right: 2cm;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #000;
            padding-top: 10px;
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

        .alert-info {
            background: #ffffff;
            position: absolute;
            height: 50px;
            width: 300px;
            box-shadow: 0 10px 15px #00000028;
            top: 0;
            right: 10px;
            z-index: 9 ;
            border-radius: 2px;
            padding: 0 5px;

            i {
                position: absolute;
                top: 25px;
                right: 10px;
                transform: translateY(-50%);
                color: var(--secondary-100);
                cursor: pointer;
                padding: 10px;
            }
            
            i:hover{
                transform:  translateY(-50%) scale(1.3);
                color: var(--secondary);
            }

            p {
                color: var(--success);
                font-size: var(--text);
                gap: 7px;
            }

            img {
                height: 15px;
            }

            .align-center {
                align-items: center;

                span {
                    margin-top: 5px;
                }
            }
        }

        .bottom {
            margin-top: 5px;
            border-top: 1px solid #000;
            display: inline-block;
            width: 20cm;
            position: absolute;
            text-align: center;
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
</head>
<body>

    <?php if(isset($_GET['status'])):  ?>
        <div class="alert-info flex item-center">
            <i class="fa fa-x"></i>
            <p class="flex align-center win">Generation reussie : <?=$student->nom?> <span><img src="assets/img/img.jpg" alt=""></span>  </p>
        </div>

    <?php endif; ?>
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
            <div class="subtitle">Année Scolaire <?=$today->format("Y")?> - <?=(int)$today->format("Y")+1?> - Fin d'année</div>
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
                        <th>Unite</th>
                        <th>Matières</th>
                        <th>Coeff.</th>
                        <th>Note/20</th>
                        <th>Note × Coeff.</th>
                        <th>Moyenne/Unité</th>
                        <th>Rang</th>
                        <th>Appréciation</th>
                        <th>Nom du Professeur</th>
                        <th>Credit</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                        $i = 0;
                        $count = 0;
                        foreach ($news as $keys => $values) {
                            $rows = count($values);
                            $count++;
                            $i = 1;
                            foreach ($values as $key => $value) {
                                $all_note_class = $app->get_note_from_classe_matiere($student->filiere, $student->classe, $student->niveau, $value->idMatiere);
                                // Find rank
                                $rank = Util::findRank($all_note_class, $student->idEtudiant);
                                array_push($ranks, $rank);
                                if($i <= 1) {
                                    echo Util::showInfoUnite($value, $rows, $rank, $mean_by_unity[$count]);

                                }else {
                                    echo Util::showInfoUnite($value, null, $rank);
                                    
                                }
                                $i++;
                            }
                        }
                    ?>
                    <tr style="font-weight: bold; background-color: #f0f0f0;">
                        <td class="subject">TOTAUX</td>
                        <td>-</td>
                        <td><?=$totaux['coef']?></td>
                        <td><?=$totaux['note']?></td>
                        <td><?=$totaux['note_coef']?></td>
                        <td><?=$totaux['means']?></td>
                        <td><?= $final_rank?></td>
                        <td>-</td>
                        <td>-</td>
                        <td><?=$totaux['credit']?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="summary-section">
            <div class="summary-title">Résultats Généraux</div>
            <div class="summary-content">
                <div class="average-box">
                    <div class="average-label">MOYENNE GÉNÉRALE</div>
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
                <div class="signature-date">Fait à Douala, le 15 Juin 2025</div>
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
        <span class="bottom">Ce relevé n'est généré qu'une seule fois</span>
    </div>
    
    <footer>

    <a href="bordereau_unite.php?id=<?=$id?>&generate=true&status">

        <button class="generate">
            Generer PDF
        </button>
    </a>
    </footer>
</body>
</html>


<?php
        $page .= "

            <style>
            @page {
                margin: 12px;
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

            footer {
                position: relative;
            }

            .footer {
                position: absolute;
                bottom: 1cm;
                left: 2cm;
                right: 2cm;
                text-align: center;
                font-size: 10px;
                border-top: 1px solid #000;
                padding-top: 10px;
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
            <div class='subtitle'>Année Scolaire " . $today->format('Y') . " - " .  (int)$today->format('Y')+1 . " - Fin d'année</div>
        </div>

        <div class='student-info'>
            <div class='left'>
                <div class='info-row'>
                    <span class='label'>Nom et Prénom:</span>
                    <span class='value'> " . strtoupper("$student->nom  $student->prenom") . " </span>
                </div>
                <div class='info-row'>
                    <span class='label'>Identifiant:</span>
                    <span class='value'> " . str_pad($student->idEtudiant, 6, 0, STR_PAD_LEFT) . "</span>
                </div>
                <div class='info-row'>
                    <span class='label'>Date de naissance:</span>
                    <span class='value'>" .  $date->format("d M Y") . "</span>
                </div>
                <div class='info-row'>
                    <span class='label'>Lieu de naissance:</span>
                    <span class='value'> $student->lieuNaissance</span>
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
                    <span class='value'> " . $today->format("d M Y") . "</span>
                </div>
            </div>
        </div>

        <div class='results-section'>
            <div class='section-title'>Détail des Notes par Matière</div>
            <table>
                <thead>
                    <tr>
                        <th>Unite</th>
                        <th>Matières</th>
                        <th>Coeff.</th>
                        <th>Note/20</th>
                        <th>Note × Coeff.</th>
                        <th>Moyenne/Unité</th>
                        <th>Rang</th>
                        <th>Appréciation</th>
                        <th>Nom du Professeur</th>
                        <th>Credit</th>
                    </tr>
                </thead>
                <tbody>";

                        $i = 0;
                        $count = 0;
                        foreach ($news as $keys => $values) {
                            $rows = count($values);
                            $count++;
                            $i = 1;
                            foreach ($values as $key => $value) {
                                $all_note_class = $app->get_note_from_classe_matiere($student->filiere, $student->classe, $student->niveau, $value->idMatiere);
                                // Find rank
                                $rank = Util::findRank($all_note_class, $student->idEtudiant);
                                array_push($ranks, $rank);
                                if($i <= 1) {
                                    $page .= Util::showInfoUnite($value, $rows, $rank, $mean_by_unity[$count]);
                                }else {
                                    $page .= Util::showInfoUnite($value, null, $rank);
                                    
                                }
                                $i++;
                            }
                        }
                    
                        $page .= "
                        <tr style='font-weight: bold; background-color: #f0f0f0;'>
                            <td class='subject'>TOTAUX</td>
                            <td>-</td>
                            <td>" . $totaux['coef'] . "</td>
                            <td>" . $totaux['note'] . "?></td>
                            <td>" . $totaux['note_coef'] . "</td>
                            <td>" . $totaux['means'] . "</td>
                            <td>" . $final_rank . "</td>
                            <td>-</td>
                            <td>-</td>
                            <td>" . $totaux['credit'] . "</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        <div class='summary-section'>
            <div class='summary-title'>Résultats Généraux</div>
            <div class='summary-content'>
                <div class='average-box'>
                    <div class='average-label'>MOYENNE GÉNÉRALE</div>
                    <div class='average-value'>" . Util::format_number($totaux['note_coef'] / $totaux['coef']) . "/" . $MAX . "</div>
                </div>
                <div class='mention-box'>
                    <div class='mention-label'>MENTION</div>
                    <div class='mention-value'>" . Util::getAppreciation($totaux['note_coef'] / $totaux['coef']) . "</div>
                </div>
                <div class='average-box'>
                    <div class='average-label'>RANG DANS LA CLASSE</div>
                    <div class='average-value'>$final_rank " . ($final_rank == 1 ? "er" : "eme") . " / " . "$effectif</div>
                </div>
            </div>
        </div>

        <div class='signatures'>
            <div class='signature-block'>
                <div class='signature-title'>Le Chef d'Établissement</div>
                <div class='signature-date'>Fait à Douala, le 15 Juin 2025</div>
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
    </div>";


// $snappy = new Pdf('"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe"');

// $snappy->setOption('page-size', 'A4');
// $snappy->setOption('zoom', 1.3); // Corrige les problèmes de taille
// $snappy->setOption('dpi', 300);  // Améliore la qualité

// $snappy->generateFromHtml($page, "jason.pdf");


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