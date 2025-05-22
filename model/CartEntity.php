<?php

class CartEntity {
    private ?int $id;
    private int $userId;
    private int $eventId;
    private ?DateTime $addedAt;

    public function __construct(
        ?int $id,
        int $userId,
        int $eventId,
        ?DateTime $addedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->eventId = $eventId;
        $this->addedAt = $addedAt;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function getEventId(): int {
        return $this->eventId;
    }

    public function getAddedAt(): ?DateTime {
        return $this->addedAt;
    }
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setUserId(int $userId): void {
        $this->userId = $userId;
    }

    public function setEventId(int $eventId): void {
        $this->eventId = $eventId;
    }

    public function setAddedAt(?DateTime $addedAt): void {
        $this->addedAt = $addedAt;
    }
}