<?php
class Oportunity {
    private ?int $id;
    private ?string $titre;
    private ?string $type_job;
    private ?string $description;
    private ?string $localisation;
    private ?DateTime $datePublication;
    private ?string $statut;

    // Constructor
    public function __construct(
        ?int $id,
        ?string $titre,
        ?string $type_job,
        ?string $description,
        ?string $localisation,
        ?DateTime $datePublication,
        ?string $statut
    ) {
        $this->id = $id;
        $this->titre = $titre;
        $this->type_job = $type_job;
        $this->description = $description;
        $this->localisation = $localisation;
        $this->datePublication = $datePublication;
        $this->statut = $statut;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Type Job</th>
                <th>Description</th>
                <th>Localisation</th>
                <th>Date Publication</th>
                <th>Statut</th>
              </tr>";
        echo "<tr>";
        echo "<td>{$this->id}</td>";
        echo "<td>{$this->titre}</td>";
        echo "<td>{$this->type_job}</td>";
        echo "<td>{$this->description}</td>";
        echo "<td>{$this->localisation}</td>";
        echo "<td>" . ($this->datePublication ? $this->datePublication->format('Y-m-d') : '') . "</td>";
        echo "<td>{$this->statut}</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Getters and Setters

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getTitre(): ?string {
        return $this->titre;
    }

    public function setTitre(?string $titre): void {
        $this->titre = $titre;
    }

    public function getTypeJob(): ?string {
        return $this->type_job;
    }

    public function setTypeJob(?string $type_job): void {
        $this->type_job = $type_job;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getLocalisation(): ?string {
        return $this->localisation;
    }

    public function setLocalisation(?string $localisation): void {
        $this->localisation = $localisation;
    }

    public function getDatePublication(): ?DateTime {
        return $this->datePublication;
    }

    public function setDatePublication(?DateTime $datePublication): void {
        $this->datePublication = $datePublication;
    }

    public function getStatut(): ?string {
        return $this->statut;
    }

    public function setStatut(?string $statut): void {
        $this->statut = $statut;
    }
}
?>