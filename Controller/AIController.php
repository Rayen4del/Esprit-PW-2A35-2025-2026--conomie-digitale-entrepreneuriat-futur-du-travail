<?php
require_once __DIR__ . '/../Service/AIService.php';

class AiController {

    private $service;

    public function __construct() {
        $this->service = new AiService();
    }

    public function generate($titre, $description) {

        // 1. Appel API
        $response = $this->service->generateFormation($titre, $description);

        $json = json_decode($response, true);

        // 2. Vérifier structure API
        if (!isset($json['choices'][0]['message']['content'])) {
            return [
                "success" => false,
                "error" => "Réponse API invalide",
                "raw" => $response
            ];
        }

        // 3. Récupérer contenu IA
        $content = $json['choices'][0]['message']['content'];

        // 4. Nettoyer markdown ```json
        $content = str_replace("```json", "", $content);
        $content = str_replace("```", "", $content);
        $content = trim($content);

        // 5. Convertir en JSON PHP
        $formation = json_decode($content, true);

        if (!$formation) {
            return [
                "success" => false,
                "error" => "JSON IA invalide",
                "raw" => $content
            ];
        }

        // 6. Succès
        return [
            "success" => true,
            "data" => $formation
        ];
    }
}