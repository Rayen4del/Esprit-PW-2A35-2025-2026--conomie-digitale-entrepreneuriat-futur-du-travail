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
                'message' => 'Could not read the CV link. Use a public HTML or text CV link for AI rating.',
                'ai_used' => false,
                'score' => 0,
                'strengths' => [],
                'gaps' => [],
                'recommendation' => 'Check that the CV link is public and readable.'
            ];
        }

        if ($this->apiKey === '') {
            return $this->fallbackScore($application, $cvText, $requirements, 'AI key is missing. Showing local estimate.');
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
                throw new Exception('Invalid AI response');
            }

            return [
                'success' => true,
                'ai_used' => true,
                'score' => max(0, min(100, (int)($decoded['score'] ?? 0))),
                'summary' => $decoded['summary'] ?? 'Compatibility scored.',
                'strengths' => array_slice($decoded['strengths'] ?? [], 0, 4),
                'gaps' => array_slice($decoded['gaps'] ?? [], 0, 4),
                'recommendation' => $decoded['recommendation'] ?? 'Review manually before deciding.'
            ];
        } catch (Exception $e) {
            return $this->fallbackScore($application, $cvText, $requirements, 'AI unavailable. Showing local estimate.', $e->getMessage());
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

        return "You are an HR screening assistant for Skiller. Rate how compatible this candidate CV is with the requirements.\n"
            . "Return only JSON in this exact shape: {\"score\":85,\"summary\":\"one short sentence\",\"strengths\":[\"...\"],\"gaps\":[\"...\"],\"recommendation\":\"short next step\"}.\n"
            . "The score must be an integer from 0 to 100. Be fair and use the requirements as the main rubric.\n\n"
            . "Requirements:\n" . json_encode(array_values($requirements), JSON_UNESCAPED_UNICODE) . "\n\n"
            . "Candidate CV content fetched from the submitted CV link:\n" . $cvText . "\n\n"
            . "Application context:\n" . json_encode($applicationData, JSON_UNESCAPED_UNICODE);
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
            throw new Exception($error ?: 'Gemini request failed');
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            throw new Exception('Invalid Gemini JSON');
        }

        if (isset($decoded['error'])) {
            throw new Exception($decoded['error']['message'] ?? 'Gemini API error');
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
            'recommendation' => 'Use this as a rough estimate only.',
            'error_detail' => $errorDetail
        ];
    }
}
