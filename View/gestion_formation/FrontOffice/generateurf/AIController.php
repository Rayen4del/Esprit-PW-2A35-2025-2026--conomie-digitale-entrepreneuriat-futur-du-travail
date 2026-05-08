<?php

require_once 'AIService.php';

class AiController {

    private $service;

    public function __construct() {
        $this->service = new AiService();
    }

    public function generateFromContent($content)
    {
        return $this->service->generateFormationFromContent($content);
    }
}