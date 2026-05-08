<?php
class Test {
    private ?int $id;
    private ?int $chapitreId;
    private ?int $formationId;
    private ?int $scoreMin;
    private ?DateTime $dateCreation;

    // Constructor
    public function __construct(
        ?int $id,
        ?int $chapitreId,
        ?int $formationId,
        ?int $scoreMin,
        ?DateTime $dateCreation
    ) {
        $this->id = $id;
        $this->chapitreId = $chapitreId;
        $this->formationId = $formationId;
        $this->scoreMin = $scoreMin;
        $this->dateCreation = $dateCreation;
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getChapitreId(): ?int {
        return $this->chapitreId;
    }

    public function getFormationId(): ?int {
        return $this->formationId;
    }

    public function getScoreMin(): ?int {
        return $this->scoreMin;
    }

    public function getDateCreation(): ?DateTime {
        return $this->dateCreation;
    }

    // Setters
    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setChapitreId(?int $chapitreId): void {
        $this->chapitreId = $chapitreId;
    }

    public function setFormationId(?int $formationId): void {
        $this->formationId = $formationId;
    }

    public function setScoreMin(?int $scoreMin): void {
        if ($scoreMin < 0 || $scoreMin > 100) {
            throw new Exception("Le score doit être entre 0 et 100");
        }
        $this->scoreMin = $scoreMin;
    }

    public function setDateCreation(?DateTime $dateCreation): void {
        $this->dateCreation = $dateCreation;
    }
}
?>