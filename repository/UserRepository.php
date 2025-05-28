<?php
require_once __DIR__ . '/../model/UserEntity.php';
require_once __DIR__ . '/../repository/Repository.php';

class UserRepository extends Repository {
    protected function getTableName(): string {
        return 'users';
    }

    public function find(int $id): ?object {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new UserEntity(
            $data['id'],
            $data['username'],
            $data['password'],
            $data['mail'],
            $data['salt'],
            $data['is_verified'] ?? null,
            $data['token'] ?? null
        );
    }

    public function findAll(): array {
        $stmt = $this->connection->query("SELECT * FROM users");
        $users = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = [
                'id' => $data['id'] ?? null,
                'username' => $data['username'],
                'password' => $data['password'],
                'mail' => $data['mail'],
                'salt' => $data['salt'],
                'is_verified' => $data['is_verified'],
                'token' => $data['token']
            ];
        }

        return $users;
    }

    public function save($entity): bool {
        if (!$entity instanceof UserEntity) {
            return false;
        }

        if ($entity->getId() === 0) {
            return $this->insert($entity);
        } else {
            return $this->update($entity);
        }
    }

    public function delete(int $id): bool {
        $stmt = $this->connection->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
    public function findByUsername(string $username): ?UserEntity {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new UserEntity(
            $data['id'],
            $data['username'],
            $data['password'],
            $data['mail'],
            $data['salt'],
            $data['is_verified'],
            $data['token']
        );
    }

    public function findByToken(string $token): ?UserEntity {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE token = :token");
        $stmt->execute([':token' => $token]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return new UserEntity(
            $data['id'],
            $data['username'],
            $data['password'],
            $data['mail'],
            $data['salt'],
            $data['is_verified'],
            $data['token'] ?? null

        );
    }

    private function insert(UserEntity $user): bool {
        $stmt = $this->connection->prepare(
            "INSERT INTO users (username, password, mail, salt, token, is_verified) 
             VALUES (:username, :password, :mail, :salt, :token, :is_verified)"
        );

        $success = $stmt->execute([
            ':username' => $user->getUsername(),
            ':password' => $user->getPassword(),
            ':mail' => $user->getMail(),
            ':salt' => $user->getSalt(),
            ':token' => $user->getToken(),
            ':is_verified' => $user->getIsVerified(),
        ]);

        if ($success) {
            $user->setId($this->connection->lastInsertId());
        }

        return $success;
    }

    private function update(UserEntity $user): bool {
        $stmt = $this->connection->prepare(
            "UPDATE users SET 
                username = :username, 
                password = :password, 
                mail = :mail, 
                salt = :salt, 
                token = :token,
                is_verified = :is_verified
             WHERE id = :id"
        );

        return $stmt->execute([
            ':id' => $user->getId(),
            ':username' => $user->getUsername(),
            ':password' => $user->getPassword(),
            ':mail' => $user->getMail(),
            ':salt' => $user->getSalt(),
            ':token' => $user->getToken(),
            ':is_verified' => $user->getIsVerified()
        ]);
    }

    public function verifyUser(int $userId): bool
    {
        $stmt = $this->connection->prepare(
            "UPDATE users SET is_verified = 1 WHERE id = :id"
        );
        return $stmt->execute([':id' => $userId]);
    }

}