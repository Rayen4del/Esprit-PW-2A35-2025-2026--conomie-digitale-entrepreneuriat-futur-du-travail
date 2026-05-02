<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include_once __DIR__ . '/../../../Controller/FormationController.php';

session_start();

$formation_id = $_GET['id'] ?? null;

if (!$formation_id) {
    die("ID formation manquant");
}

/* ================= GET FORMATION ================= */
$formationC = new FormationController();
$formation = $formationC->getFormationById($formation_id);

if (!$formation) {
    die("Formation introuvable");
}

/* ================= USER (OPTIONNEL) ================= */
$user_name = $_SESSION['user_name'] ?? "Utilisateur";

/* ================= DOMPDF CONFIG ================= */
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

/* ================= HTML CERTIFICAT ================= */
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body {
    font-family: Arial, sans-serif;
    text-align: center;
    border: 12px solid #2c3e50;
    padding: 60px;
    background: #fdfdfd;
}

h1 {
    font-size: 42px;
    color: #2c3e50;
    margin-bottom: 20px;
}

.sub {
    font-size: 18px;
    color: #555;
}

.name {
    font-size: 32px;
    font-weight: bold;
    margin: 25px 0;
    color: #000;
}

.course {
    font-size: 22px;
    margin-top: 10px;
    color: #2c3e50;
}

.badge {
    margin-top: 30px;
    display: inline-block;
    padding: 12px 25px;
    background: #27ae60;
    color: white;
    font-weight: bold;
    border-radius: 6px;
}

.date {
    margin-top: 40px;
    font-size: 14px;
    color: #777;
}

.footer {
    margin-top: 50px;
    font-size: 12px;
    color: #999;
}
</style>
</head>

<body>

<h1>CERTIFICAT DE RÉUSSITE</h1>

<p class="sub">Ce certificat est décerné à :</p>

<div class="name">'.$user_name.'</div>

<p class="sub">Pour avoir complété avec succès la formation :</p>

<div class="course">'.$formation['titre'].'</div>

<div class="badge">FORMATION VALIDÉE</div>

<div class="date">Date : '.date('d/m/Y').'</div>

<div class="footer">
Plateforme de formation - Skiller6
</div>

</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

/* ================= DOWNLOAD ================= */
$dompdf->stream("certificat.pdf", ["Attachment" => true]);
exit;
?>