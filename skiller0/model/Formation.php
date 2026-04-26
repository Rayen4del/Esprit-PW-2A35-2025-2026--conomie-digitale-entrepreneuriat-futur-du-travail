<?php

class Formation {
    private ?int $id;
    private ?string $titre;
    private ?string $description;
    private ?int $createdBy;
    private ?string $nomProprietaire;
    private ?DateTime $dateCreation;
    private ?float $evaluation;
    private ?string $image;
    private ?string $etat; // ✅ AJOUT

    // Constructor
    public function __construct(
        ?int $id,
        ?string $titre,
        ?string $description,
        ?int $createdBy,
        ?string $nomProprietaire,
        ?DateTime $dateCreation,
        ?float $evaluation,
        ?string $image,
        ?string $etat
    ) {
        $this->id = $id;
        $this->titre = $titre;
        $this->description = $description;
        $this->createdBy = $createdBy;
        $this->nomProprietaire = $nomProprietaire;
        $this->dateCreation = $dateCreation;
        $this->evaluation = $evaluation;
        $this->image = $image;
        $this->setEtat($etat); // ✅ validation propre
    }

    // ======================
    // GETTERS
    // ======================

    public function getId(): ?int {
        return $this->id;
    }

    public function getTitre(): ?string {
        return $this->titre;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function getCreatedBy(): ?int {
        return $this->createdBy;
    }

    public function getNomProprietaire(): ?string {
        return $this->nomProprietaire;
    }

    public function getDateCreation(): ?DateTime {
        return $this->dateCreation;
    }

    public function getEvaluation(): ?float {
        return $this->evaluation;
    }

    public function getImage(): ?string {
        return $this->image;
    }

    public function getEtat(): ?string {
        return $this->etat;
    }

    // ======================
    // SETTERS
    // ======================

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setTitre(?string $titre): void {
        $this->titre = $titre;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function setCreatedBy(?int $createdBy): void {
        $this->createdBy = $createdBy;
    }

    public function setNomProprietaire(?string $nomProprietaire): void {
        $this->nomProprietaire = $nomProprietaire;
    }

    public function setDateCreation(?DateTime $dateCreation): void {
        $this->dateCreation = $dateCreation;
    }

    public function setEvaluation(?float $evaluation): void {
        if ($evaluation !== null && ($evaluation < 0 || $evaluation > 5)) {
            throw new Exception("L'évaluation doit être entre 0 et 5");
        }
        $this->evaluation = $evaluation;
    }

    public function setImage(?string $image): void {
        $this->image = $image;
    }

    // ======================
    // ETAT (ACTIF / INACTIF)
    // ======================

    public function setEtat(?string $etat): void {
        $this->etat = $etat;
    }
}

?>