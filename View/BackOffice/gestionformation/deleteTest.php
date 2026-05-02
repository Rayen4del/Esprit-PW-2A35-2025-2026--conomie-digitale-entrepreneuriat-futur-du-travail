<?php
include __DIR__ . '/../../../Controller/TestController.php';

$testC = new TestController();

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    // (optionnel) vérifier si le test existe
    $test = $testC->getTestById($id);

    if ($test) {

        // suppression du test
        $testC->deleteTest($id);
    }
}

// retour page liste tests
header("Location: consultertests.php?deleted=1");
exit;
?>