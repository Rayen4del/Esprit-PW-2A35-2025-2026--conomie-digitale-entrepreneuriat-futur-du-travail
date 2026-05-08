<?php
class Progression {
    private ?int $id;
    private ?int $userId;
    private ?int $chapitreId;
    private ?DateTime $date;

    // Constructor
    public function __construct(
        ?int $id,
        ?int $userId,
        ?int $chapitreId,
        ?DateTime $date
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->chapitreId = $chapitreId;
        $this->date = $date;
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
}
?>