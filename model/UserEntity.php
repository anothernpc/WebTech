<?php

class UserEntity
{
    private int $id;
    private string $userName;
    private string $password;
    private string $mail;
    private string $salt;
    private ?string $token;

    public function __construct(int $id, string $userName, string $password, string $mail, string $salt, ?string $token = null) {
        $this->id = $id;
        $this->userName = $userName;
        $this->password = $password;
        $this->mail = $mail;
        $this->salt = $salt;
        $this->token = $token;
    }
    public function getId(): int {
        return $this->id;
    }

    public function getUserName(): string {
        return $this->userName;
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
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setUserName(string $userName): void {
        $this->userName = $userName;
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

    public function clone(): UserEntity {
        return new UserEntity(
            $this->id,
            $this->userName,
            $this->password,
            $this->mail,
            $this->salt,
            $this->token
        );
    }
}

