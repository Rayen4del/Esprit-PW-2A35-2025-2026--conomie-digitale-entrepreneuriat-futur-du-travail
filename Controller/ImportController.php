<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/formation.php');
include_once(__DIR__ . '/FormationController.php');

class ImportController {

    public function importFromUrl($url, $etat = "active")
    {
        $formationC = new FormationController();

        // 1. récupérer HTML
        $html = file_get_contents($url);

        if (!$html) {
            throw new Exception("Impossible de charger l'URL");
        }

        // 2. extraction simple titre
        preg_match('/<title>(.*?)<\/title>/', $html, $matches);
        $titre = $matches[1] ?? "Formation importée";

        // 3. extraction description (simple fallback)
        $description = strip_tags(substr($html, 0, 300));

        // 4. création formation
        $formation = new Formation(
            null,
            $titre,
            $description,
            1,
            "Import System",
            new DateTime(),
            0,
            "",
            $etat
        );

        $formationC->addFormation($formation);

        // 5. (OPTION) ici tu peux ajouter chapitre auto plus tard
    }
}
?>