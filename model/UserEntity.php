<?php

class UserEntity
{
    private int $id;
    private string $username;
    private string $password;
    private string $mail;
    private string $salt;
    private ?string $token;
    private bool $is_verified;

    public function __construct(int $id, string $username, string $password, string $mail, string $salt, bool $is_verified, ?string $token = null) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->mail = $mail;
        $this->salt = $salt;
        $this->token = $token;
        $this->is_verified = $is_verified;
    }
    public function getId(): int {
        return $this->id;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getMail(): string {
        return $this->mail;
    }

    public function getSalt(): string {
        return $this->salt;
    }

    public function getToken(): ?string {
        return $this->token;
    }

    public function getIsVerified(): int {
        return $this->is_verified;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setUsername(string $username): void {
        $this->username = $username;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function setMail(string $mail): void {
        $this->mail = $mail;
    }

    public function setToken(?string $token): void {
        $this->token = $token;   
    }

    public function setIsVerified(bool $is_verified): void
    {
        $this->is_verified = $is_verified;
    }

    public function clone(): UserEntity {
        return new UserEntity(
            $this->id,
            $this->username,
            $this->password,
            $this->mail,
            $this->salt,
            $this->token,
            $this->is_verified
        );
    }
}

