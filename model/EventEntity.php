<?php

class EventEntity
{
    private ?int $id;
    private string $title;
    private string $description;
    private string $date;
    private int $price;
    private ?string $image;


    public function __construct(?int $id, string $title, string $description, string $date, int $price, ?string $image = null) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->price = $price;
        $this->image = $image;
    }
    public function getId(): ?int {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getDate(): string {
        return $this->date;
    }

    public function getPrice(): int {
        return $this->price;
    }

    public function getImage(): ?string {
        return $this->image;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function setDate(string $date): void {
        $this->date = $date;
    }
    public function setPrice(int $price): void {
        $this->price = $price;
    }

    public function setImage(?string $image): void {
        $this->image = $image;
    }

    public function clone(): UserEntity {
        return new UserEntity(
            $this->id,
            $this->title,
            $this->description,
            $this->date,
            $this->price,
            $this->image
        );
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }
}