<?php
class AiService {

    public function generateFormation($titre, $description)
    {
        require_once __DIR__ . '/../config/ai.php';

        $apiKey = GROQ_API_KEY;

       $prompt = "
Tu es un expert en pédagogie et en création de formations professionnelles.

Ta mission est de générer une formation complète, claire et directement exploitable.

========================
🎯 OBJECTIF
========================
Créer une formation pour débutants avec progression logique.

========================
📚 FORMAT JSON STRICT
========================

Tu dois retourner UNIQUEMENT un JSON valide.

NE PAS AJOUTER :
- ```json
- texte
- explication

FORMAT EXACT :

{
  \"formation\": {
    \"titre\": \"string\",
    \"description\": \"string\"
  },
  \"chapitres\": [
    {
      \"titre\": \"string\",
      \"ordre\": number,
      \"contenus\": [
        {
          \"type\": \"text\",
          \"contenu\": \"string\"
        },
        {
          \"type\": \"youtube\",
          \"contenu\": \"https://www.youtube.com/watch?v=...\"
        }
      ]
    }
  ]
}

========================
📌 RÈGLES
========================

- Minimum 4 chapitres
- Maximum 6 chapitres
- Chaque chapitre contient :
  - 1 text
  - 1 youtube

- Chapitres progressifs :
  1 = introduction
  2-3 = bases
  4+ = avancé

========================
📥 DONNÉES
========================

Titre : $titre
Description : $description

========================
⚠️ IMPORTANT
========================

- JSON VALIDE OBLIGATOIRE
- PAS de texte avant ou après
- PAS de markdown
- PAS de commentaire

Si erreur → recommence correctement

========================
🎯 OUTPUT
========================

JSON uniquement.
";

        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ],
            "temperature" => 0.7
        ];

        $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ]);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }
}