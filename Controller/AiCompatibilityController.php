<?php
require_once __DIR__ . '/../config.php';

class AiCompatibilityController {
    private string $apiKey;
    private string $model = 'gemini-2.5-flash';

    public function __construct(?string $apiKey = null) {
        $this->apiKey = $apiKey ?: geminiApiKey();
    }

    public function scoreApplication(array $application, array $requirements): array {
        $cvLink = trim((string)($application['CV'] ?? $application['resource'] ?? ''));
        $cvText = $this->extractCvTextFromLink($cvLink);

        if ($cvText === '') {
            return [
                'success' => false,
                'message' => 'Impossible de lire le lien du CV. Utilisez un lien public HTML ou texte pour l evaluation IA.',
                'ai_used' => false,
                'score' => 0,
                'strengths' => [],
                'gaps' => [],
                'recommendation' => 'Verifiez que le lien du CV est public et lisible.'
            ];
        }

        if ($this->apiKey === '') {
            return $this->fallbackScore($application, $cvText, $requirements, 'La cle IA est manquante. Estimation locale affichee.');
        }

        try {
            $payload = [
                'contents' => [[
                    'parts' => [[
                        'text' => $this->buildPrompt($application, $cvText, $requirements)
                    ]]
                ]],
                'generationConfig' => [
                    'temperature' => 0.15,
                    'responseMimeType' => 'application/json'
                ]
            ];

            $response = $this->postJson(
                'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent',
                $payload
            );
            $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $decoded = $this->decodeJsonText($text);

            if (!is_array($decoded)) {
                throw new Exception('Reponse IA invalide');
            }

            return [
                'success' => true,
                'ai_used' => true,
                'score' => max(0, min(100, (int)($decoded['score'] ?? 0))),
                'summary' => $decoded['summary'] ?? 'Compatibilite evaluee.',
                'strengths' => array_slice($decoded['strengths'] ?? [], 0, 4),
                'gaps' => array_slice($decoded['gaps'] ?? [], 0, 4),
                'recommendation' => $decoded['recommendation'] ?? 'Verifier manuellement avant de decider.'
            ];
        } catch (Exception $e) {
            return $this->fallbackScore($application, $cvText, $requirements, 'IA indisponible. Estimation locale affichee.', $e->getMessage());
        }
    }

    private function buildPrompt(array $application, string $cvText, array $requirements): string {
        $applicationData = [
            'application_id' => $application['ID'] ?? '',
            'opportunity' => $application['opportunity_title'] ?? 'N/A',
            'job_type' => $application['Type_job'] ?? '',
            'motivation' => $application['motivation'] ?? '',
            'cv_link' => $application['CV'] ?? $application['resource'] ?? ''
        ];

        return "Tu es un assistant RH pour Skiller. Evalue la compatibilite du CV du candidat avec les exigences.\n"
            . "Retourne uniquement du JSON sous cette forme exacte : {\"score\":85,\"summary\":\"phrase courte en francais\",\"strengths\":[\"...\"],\"gaps\":[\"...\"],\"recommendation\":\"prochaine etape courte en francais\"}.\n"
            . "Le score doit etre un entier de 0 a 100. Sois juste et utilise les exigences comme grille principale.\n\n"
            . "Exigences :\n" . json_encode(array_values($requirements), JSON_UNESCAPED_UNICODE) . "\n\n"
            . "Contenu du CV recupere depuis le lien soumis :\n" . $cvText . "\n\n"
            . "Contexte de la candidature :\n" . json_encode($applicationData, JSON_UNESCAPED_UNICODE);
    }

    private function extractCvTextFromLink(string $cvLink): string {
        if ($cvLink === '') {
            return '';
        }

        $content = $this->readCvLink($cvLink);
        if ($content === '') {
            return '';
        }

        $contentType = $this->lastContentType ?? '';
        if (stripos($contentType, 'pdf') !== false) {
            return '';
        }

        $text = html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim(mb_substr($text, 0, 8000));
    }

    private ?string $lastContentType = null;

    private function readCvLink(string $cvLink): string {
        $this->lastContentType = null;

        if (preg_match('#^https?://#i', $cvLink)) {
            return $this->readRemoteCv($cvLink);
        }

        $path = $cvLink;
        if (strpos($path, appBasePath()) === 0) {
            $path = substr($path, strlen(appBasePath()));
        }

        $path = ltrim(parse_url($path, PHP_URL_PATH) ?? $path, '/');
        $fullPath = realpath(__DIR__ . '/../' . $path);
        $projectRoot = realpath(__DIR__ . '/..');

        if (!$fullPath || strpos($fullPath, $projectRoot) !== 0 || !is_file($fullPath)) {
            return '';
        }

        return file_get_contents($fullPath) ?: '';
    }

    private function readRemoteCv(string $cvLink): string {
        if (function_exists('curl_init')) {
            $ch = curl_init($cvLink);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'Skiller CV Compatibility Checker',
                CURLOPT_MAXREDIRS => 3
            ]);
            $content = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $this->lastContentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: '';
            curl_close($ch);

            return ($content !== false && $statusCode >= 200 && $statusCode < 300) ? $content : '';
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'follow_location' => 1,
                'header' => "User-Agent: Skiller CV Compatibility Checker\r\n"
            ]
        ]);

        return file_get_contents($cvLink, false, $context) ?: '';
    }

    private function postJson(string $url, array $payload): array {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);

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

    private function fallbackScore(array $application, string $cvText, array $requirements, string $message, string $errorDetail = ''): array {
        $haystack = strtolower($cvText . ' ' . ($application['motivation'] ?? '') . ' ' . ($application['opportunity_title'] ?? '') . ' ' . ($application['Type_job'] ?? ''));
        $matched = [];
        $missing = [];

        foreach ($requirements as $requirement) {
            $words = preg_split('/\W+/', strtolower($requirement));
            $hit = false;
            foreach ($words as $word) {
                if (strlen($word) > 3 && strpos($haystack, $word) !== false) {
                    $hit = true;
                    break;
                }
            }

            if ($hit) {
                $matched[] = $requirement;
            } else {
                $missing[] = $requirement;
            }
        }

        $score = count($requirements) > 0 ? round((count($matched) / count($requirements)) * 100) : 0;

        return [
            'success' => true,
            'ai_used' => false,
            'score' => $score,
            'summary' => $message,
            'strengths' => array_slice($matched, 0, 4),
            'gaps' => array_slice($missing, 0, 4),
            'recommendation' => 'Utilisez ce score uniquement comme estimation approximative.',
            'error_detail' => $errorDetail
        ];
    }
}
