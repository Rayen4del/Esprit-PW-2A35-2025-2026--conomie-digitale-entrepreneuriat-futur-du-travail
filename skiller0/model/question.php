<?php
class Question {
    private ?int $id;
    private ?int $testId;
    private ?string $type;
    private ?string $contenu;

    // Types possibles 🔥
    const TYPE_QCM = "qcm";
    const TYPE_TRUE_FALSE = "true_false";
    const TYPE_TEXT = "text";

    // Constructor
    public function __construct(
        ?int $id,
        ?int $testId,
        ?string $type,
        ?string $contenu
    ) {
        $this->id = $id;
        $this->testId = $testId;
        $this->type = $type;
        $this->contenu = $contenu;
    }

    // Getters
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

    // Setters
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
            throw new Exception("Type de question invalide");
        }

        $this->type = $type;
    }

    public function setContenu(?string $contenu): void {
        $this->contenu = $contenu;
    }
}
?>