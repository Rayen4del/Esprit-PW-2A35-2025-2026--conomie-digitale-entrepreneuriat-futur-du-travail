<?php
require_once __DIR__ . '/../config.php';

class RequirementController {
    private string $storagePath;

    public function __construct(?string $storagePath = null) {
        $this->storagePath = $storagePath ?: __DIR__ . '/../data/compatibility_requirements.json';
        $this->ensureStorageExists();
    }

    public function getRequirements(): array {
        $content = file_get_contents($this->storagePath);
        $decoded = json_decode($content ?: '[]', true);
        return is_array($decoded) ? array_values(array_filter($decoded, 'strlen')) : $this->defaultRequirements();
    }

    public function saveRequirements(string $rawRequirements): array {
        $requirements = preg_split('/\r\n|\r|\n/', $rawRequirements);
        $requirements = array_map('trim', $requirements);
        $requirements = array_values(array_filter($requirements, function ($item) {
            return $item !== '';
        }));

        if (empty($requirements)) {
            $requirements = $this->defaultRequirements();
        }

        file_put_contents($this->storagePath, json_encode($requirements, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $requirements;
    }

    public function requirementsText(array $requirements): string {
        return implode("\n", $requirements);
    }

    private function ensureStorageExists(): void {
        $directory = dirname($this->storagePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        if (!file_exists($this->storagePath)) {
            file_put_contents($this->storagePath, json_encode($this->defaultRequirements(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    private function defaultRequirements(): array {
        return [
            'Bonne maitrise de PHP avec une architecture MVC',
            'Experience dans la creation de fonctionnalites CRUD avec MySQL et PDO',
            'Competences frontend en HTML, CSS, JavaScript et Bootstrap',
            'Capacite a consommer ou integrer des API REST',
            'Communication claire et bonnes habitudes de documentation',
            'Au moins un projet web complet realise'
        ];
    }
}
