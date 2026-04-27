<?php

class Question {

    private ?int $id;
    private ?int $testId;
    private ?string $type;
    private ?string $contenu;
    private ?string $reponse;

    // Types possibles
    const TYPE_QCM = "qcm";
    const TYPE_TRUE_FALSE = "true_false";
    const TYPE_TEXT = "text";

    public function __construct(
        ?int $id,
        ?int $testId,
        ?string $type,
        ?string $contenu,
        ?string $reponse
    ) {
        $this->id = $id;
        $this->testId = $testId;
        $this->type = $type;
        $this->contenu = $contenu;
        $this->reponse = $reponse; // ✅ FIX IMPORTANT
    }

    // GETTERS
    public function getId(): ?int {
        return $this->id;
    }

    public function getTestId(): ?int {
        return $this->testId;
    }

    public function getType(): ?string {
        return $this->type;
    }

    public function getContenu(): ?string {
        return $this->contenu;
    }

    public function getReponse(): ?string {
        return $this->reponse;
    }

    // SETTERS
    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setTestId(?int $testId): void {
        $this->testId = $testId;
    }

    public function setType(?string $type): void {

        $typesValides = [
            self::TYPE_QCM,
            self::TYPE_TRUE_FALSE,
            self::TYPE_TEXT
        ];

        if (!in_array($type, $typesValides)) {
            throw new Exception("Type invalide");
        }

        $this->type = $type;
    }

    public function setContenu(?string $contenu): void {
        $this->contenu = $contenu;
    }

    public function setReponse(?string $reponse): void {
        $this->reponse = $reponse;
    }
}
?>