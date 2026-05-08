<?php
class Sponsoring {
    private ?int $id_sp;
    private ?int $id_u;
    private ?string $nom_ent;
    private ?string $logo_entp;
    private ?DateTime $date_deb;
    private ?DateTime $date_fin;
    private ?string $mail_event;

    // Constructor
    public function __construct(
        ?int $id_sp,
        ?int $id_u,
        ?string $nom_ent,
        ?string $logo_entp,
        ?DateTime $date_deb,
        ?DateTime $date_fin,
        ?string $mail_event
    ) {
        $this->id_sp = $id_sp;
        $this->id_u = $id_u;
        $this->nom_ent = $nom_ent;
        $this->logo_entp = $logo_entp;
        $this->date_deb = $date_deb;
        $this->date_fin = $date_fin;
        $this->mail_event = $mail_event;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>
                <th>ID Sponsoring</th>
                <th>ID User</th>
                <th>Nom Entreprise</th>
                <th>Logo Entreprise</th>
                <th>Date Début</th>
                <th>Date Fin</th>
                <th>Email Event</th>
              </tr>";
        echo "<tr>";
        echo "<td>{$this->id_sp}</td>";
        echo "<td>{$this->id_u}</td>";
        echo "<td>{$this->nom_ent}</td>";
        echo "<td>{$this->logo_entp}</td>";
        echo "<td>" . ($this->date_deb ? $this->date_deb->format('Y-m-d') : '') . "</td>";
        echo "<td>" . ($this->date_fin ? $this->date_fin->format('Y-m-d') : '') . "</td>";
        echo "<td>{$this->mail_event}</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Getters and Setters

    public function getIdSp(): ?int {
        return $this->id_sp;
    }

    public function setIdSp(?int $id_sp): void {
        $this->id_sp = $id_sp;
    }

    public function getIdU(): ?int {
        return $this->id_u;
    }

    public function setIdU(?int $id_u): void {
        $this->id_u = $id_u;
    }

    public function getNomEnt(): ?string {
        return $this->nom_ent;
    }

    public function setNomEnt(?string $nom_ent): void {
        $this->nom_ent = $nom_ent;
    }

    public function getLogoEntp(): ?string {
        return $this->logo_entp;
    }

    public function setLogoEntp(?string $logo_entp): void {
        $this->logo_entp = $logo_entp;
    }

    public function getDateDeb(): ?DateTime {
        return $this->date_deb;
    }

    public function setDateDeb(?DateTime $date_deb): void {
        $this->date_deb = $date_deb;
    }

    public function getDateFin(): ?DateTime {
        return $this->date_fin;
    }

    public function setDateFin(?DateTime $date_fin): void {
        $this->date_fin = $date_fin;
    }

    public function getMailEvent(): ?string {
        return $this->mail_event;
    }

    public function setMailEvent(?string $mail_event): void {
        $this->mail_event = $mail_event;
    }
}
?> 