<?php
require_once __DIR__ . '/../config.php';

class AiSearchController {
    private string $apiKey;
    private string $model = 'gemini-2.5-flash';

    public function __construct(?string $apiKey = null) {
        $this->apiKey = $apiKey ?: geminiApiKey();
    }

    public function searchOpportunities(string $query, array $opportunities): array {
        $query = trim($query);
        if ($query === '') {
            return [
                'success' => false,
                'message' => 'Veuillez saisir une recherche',
                'results' => [],
                'count' => 0,
                'ai_used' => false
            ];
        }

        if ($this->apiKey === '') {
            return $this->fallbackSearch($query, $opportunities, 'La cle IA est manquante. Resultats locaux affiches.');
        }

        try {
            $aiResponse = $this->askGemini($query, $opportunities);
            $results = $this->mapAiMatchesToOpportunities($aiResponse['matches'] ?? [], $opportunities);

            if (empty($results)) {
                return [
                    'success' => true,
                    'message' => $aiResponse['message'] ?? 'L IA n a trouve aucune opportunite correspondante.',
                    'results' => [],
                    'count' => 0,
                    'ai_used' => true
                ];
            }

            return [
                'success' => true,
                'message' => $aiResponse['message'] ?? ('L IA a trouve ' . count($results) . ' opportunite(s) pertinente(s).'),
                'results' => $results,
                'count' => count($results),
                'ai_used' => true
            ];
        } catch (Exception $e) {
            return $this->fallbackSearch($query, $opportunities, 'L IA est indisponible pour le moment. Resultats locaux affiches.', $e->getMessage());
        }
    }

    private function askGemini(string $query, array $opportunities): array {
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent';
        $prompt = $this->buildPrompt($query, $opportunities);
        $payload = [
            'contents' => [[
                'parts' => [[
                    'text' => $prompt
                ]]
            ]],
            'generationConfig' => [
                'temperature' => 0.2,
                'responseMimeType' => 'application/json'
            ]
        ];

        $response = $this->postJson($endpoint, $payload);
        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $decoded = $this->decodeJsonText($text);

        if (!is_array($decoded)) {
            throw new Exception('Reponse IA invalide');
        }

        return $decoded;
    }

    private function buildPrompt(string $query, array $opportunities): string {
        $compactOpportunities = array_map(function ($opportunity) {
            return [
                'id' => (int)($opportunity['ID'] ?? 0),
                'title' => $opportunity['Titre'] ?? '',
                'type' => $opportunity['Type_job'] ?? '',
                'location' => $opportunity['Localisation'] ?? '',
                'status' => $opportunity['Statut'] ?? '',
                'published' => $opportunity['datePublication'] ?? '',
                'description' => mb_substr((string)($opportunity['Description'] ?? ''), 0, 700)
            ];
        }, array_slice($opportunities, 0, 80));

        return "Tu es l assistant de recherche d emploi de Skiller. Associe la demande en langage naturel de l utilisateur aux opportunites les plus pertinentes.\n"
            . "Retourne uniquement du JSON sous cette forme : {\"message\":\"phrase courte et utile en francais\",\"matches\":[{\"id\":123,\"reason\":\"raison breve en francais\"}]}.\n"
            . "Utilise uniquement les IDs des opportunites fournies. Retourne au maximum 10 resultats, tries du meilleur au moins bon.\n\n"
            . "Demande utilisateur : " . $query . "\n\n"
            . "Opportunites JSON :\n" . json_encode($compactOpportunities, JSON_UNESCAPED_UNICODE);
    }

    private function postJson(string $url, array $payload): array {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'x-goog-api-key: ' . $this->apiKey
                ],
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_TIMEOUT => 20
            ]);

            $responseBody = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($responseBody === false || $statusCode < 200 || $statusCode >= 300) {
                throw new Exception($error ?: 'Echec de la requete Gemini');
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\nx-goog-api-key: " . $this->apiKey . "\r\n",
                    'content' => $body,
                    'timeout' => 20,
                    'ignore_errors' => true
                ]
            ]);
            $responseBody = file_get_contents($url, false, $context);

            if ($responseBody === false) {
                throw new Exception('Echec de la requete Gemini');
            }
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            throw new Exception('JSON Gemini invalide');
        }

        if (isset($decoded['error'])) {
            throw new Exception($decoded['error']['message'] ?? 'Erreur API Gemini');
        }

        return $decoded;
    }

    private function decodeJsonText(string $text): ?array {
        $text = trim($text);
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        $decoded = json_decode($text, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function mapAiMatchesToOpportunities(array $matches, array $opportunities): array {
        $byId = [];
        foreach ($opportunities as $opportunity) {
            $byId[(int)($opportunity['ID'] ?? 0)] = $opportunity;
        }

        $results = [];
        foreach ($matches as $match) {
            $id = (int)($match['id'] ?? 0);
            if (!isset($byId[$id])) {
                continue;
            }

            $opportunity = $byId[$id];
            $opportunity['ai_reason'] = $match['reason'] ?? '';
            $results[] = $opportunity;
        }

        return $results;
    }

    private function fallbackSearch(string $query, array $opportunities, string $message, string $errorDetail = ''): array {
        $results = [];
        $queryWords = preg_split('/\s+/', strtolower($query));

        foreach ($opportunities as $opp) {
            $score = 0;
            $content = strtolower(($opp['Titre'] ?? '') . ' ' . ($opp['Description'] ?? '') . ' ' . ($opp['Type_job'] ?? '') . ' ' . ($opp['Localisation'] ?? ''));

            foreach ($queryWords as $word) {
                if (strlen($word) > 2) {
                    $score += substr_count($content, $word) * 10;
                    if (stripos($opp['Titre'] ?? '', $word) !== false) {
                        $score += 5;
                    }
                }
            }

            if ($score > 0) {
                $results[] = ['opp' => $opp, 'score' => $score];
            }
        }

        usort($results, function ($a, $b) {
            return $b['score'] - $a['score'];
        });

        $cleanResults = array_map(function ($item) {
            return $item['opp'];
        }, array_slice($results, 0, 10));

        return [
            'success' => true,
            'message' => $message,
            'results' => $cleanResults,
            'count' => count($cleanResults),
            'ai_used' => false,
            'error_detail' => $errorDetail
        ];
    }
}
