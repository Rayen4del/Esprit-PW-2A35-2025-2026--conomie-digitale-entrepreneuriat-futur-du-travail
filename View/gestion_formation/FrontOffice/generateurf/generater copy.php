<?php
require_once 'AiService.php';

$ai = new AiService();

$html = null;

if (!empty($_POST['content'])) {
    $html = $ai->generateFormationHTML($_POST['content']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Formation IA HTML</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .formation {
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        .chapitre {
            margin-bottom: 20px;
            padding: 15px;
            border-left: 5px solid #0d6efd;
            background: #f8f9fa;
        }
    </style>
</head>

<body class="bg-light">

<div class="container py-4">

    

</div>

</body>
</html>