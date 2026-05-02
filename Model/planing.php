<?php
class Planing {
    private ?int $id;
    private ?int $userId;
    private ?int $chapitreId;
    private ?DateTime $date;
    private ?string $note;

    // Constructor
    public function __construct(
        ?int $id,
        ?int $userId,
        ?int $chapitreId,
        ?DateTime $date,
        ?string $note
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->chapitreId = $chapitreId;
        $this->date = $date;
        $this->note = $note;
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getUserId(): ?int {
        return $this->userId;
    }

    public function getChapitreId(): ?int {
        return $this->chapitreId;
    }

    public function getDate(): ?DateTime {
        return $this->date;
    }

    public function getNote(): ?string {
        return $this->note;
    }

    // Setters
    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setUserId(?int $userId): void {
        $this->userId = $userId;
    }

    public function setChapitreId(?int $chapitreId): void {
        $this->chapitreId = $chapitreId;
    }

    public function setDate(?DateTime $date): void {
        $this->date = $date;
    }

    public function setNote(?string $note): void {
        $this->note = $note;
    }
}
?>