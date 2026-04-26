<?php
class Chapitre {
    private ?int $id;
    private ?int $formationId;
    private ?string $titre;
    private ?int $ordre;

    // Constructor
    public function __construct(
        ?int $id,
        ?int $formationId,
        ?string $titre,
        ?int $ordre
    ) {
        $this->id = $id;
        $this->formationId = $formationId;
        $this->titre = $titre;
        $this->ordre = $ordre;
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getFormationId(): ?int {
        return $this->formationId;
    }

    public function getTitre(): ?string {
        return $this->titre;
    }

    public function getOrdre(): ?int {
        return $this->ordre;
    }

    // Setters
    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setFormationId(?int $formationId): void {
        $this->formationId = $formationId;
    }

    public function setTitre(?string $titre): void {
        $this->titre = $titre;
    }

    public function setOrdre(?int $ordre): void {
        $this->ordre = $ordre;
    }
}
?>