<?php

require_once 'ai.php';

class AiService
{
    public function generateFormationHTML($content)
    {
        $prompt = $this->buildPrompt($content);

        $response = $this->callGroq($prompt);

        return $this->extractText($response);
    }

    private function buildPrompt($content)
    {
        return <<<EOT
Tu es un professeur expert en programmation.

Génère une formation complète sur : "$content"

⚠️ IMPORTANT:
- Retourne UNIQUEMENT du HTML
- Pas de JSON
- Pas de markdown
- Style pédagogique et coloré

STRUCTURE OBLIGATOIRE:

<div class="formation">

<h1 style="color:#0d6efd;">Titre formation</h1>

<p style="background:#f8f9fa;padding:10px;border-left:4px solid #0d6efd;">
Description complète de la formation
</p>

<h2 style="color:#198754;">📚 Chapitres</h2>

<div class="chapitre">
<h3 style="color:#dc3545;">Chapitre 1</h3>

<p style="color:#333;">
Explication détaillée
</p>

<pre style="background:#212529;color:#fff;padding:10px;">
Code exemple
</pre>

<p style="background:#fff3cd;padding:10px;">
Cas d'utilisation réel
</p>

</div>

</div>

Règles:
- 5 à 10 chapitres
- explications longues
- exemples de code
- cas réel entreprise
EOT;
    }

    private function callGroq($prompt)
    {
        $data = [
            "model" => "openai/gpt-oss-120b",
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ]
        ];

        return $this->curl(
            "https://api.groq.com/openai/v1/chat/completions",
            $data,
            GROQ_API_KEY
        );
    }

    private function curl($url, $data, $key)
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer $key"
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);

        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    private function extractText($response)
    {
        $json = json_decode($response, true);

        return $json['choices'][0]['message']['content'] ?? "<p>Erreur IA</p>";
    }
}