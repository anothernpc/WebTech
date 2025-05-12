<?php

class CartEntity
{
    private ?int $id;
    private string $title;
    private string $date;
    private int $price;
    private int $quantity;
    private int $subtotal;


    public function __construct(?int $id, string $title, string $date, int $price, int $quantity, int $subtotal) {
        $this->id = $id;
        $this->title = $title;
        $this->date = $date;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->subtotal = $subtotal;
    }
    public function getId(): ?int {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getDate(): string {
        return $this->date;
    }

    public function getPrice(): int {
        return $this->price;
    }

    public function getQuantity(): int {
        return $this->quantity;
    }

    public function getSubtotal(): int {
        return $this->subtotal;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function setDate(string $date): void {
        $this->date = $date;
    }
    public function setPrice(int $price): void {
        $this->price = $price;
    }

    public function setQuantity(int $quantity): void {
        $this->quantity = $quantity;
    }

    public function setSubtotal(int $subtotal): void {
        $this->subtotal = $subtotal;
    }

    public function clone(): UserEntity {
        return new UserEntity(
            $this->id,
            $this->title,
            $this->date,
            $this->price,
            $this->quantity,
            $this->subtotal
        );
    }
}