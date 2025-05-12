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
            $data['user_name'],
            $data['password'],
            $data['mail'],
            $data['salt'],
            $data['token'] ?? null
        );
    }

    public function findAll(): array {
        $stmt = $this->connection->query("SELECT * FROM users");
        $users = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = [
                'id' => $data['id'] ?? null,
                'title' => $data['title'],
                'date' => $data['date'],
                'price' => (int)$data['price'],
                'quantity' => (int)$data['quantity'],
                'subtotal' => (int)$data['subtotal'],
            ];
        }

        return $users;
    }

    public function save($entity): bool {
        if (!$entity instanceof UserEntity) {
            return false;
        }

        if ($entity->getId() === null) {
            return $this->insert($entity);
        } else {
            return $this->update($entity);
        }
    }

    public function delete(int $id): bool {
        $stmt = $this->connection->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Специфичные методы для User
    public function findByUsername(string $username): ?UserEntity {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE user_name = :username");
        $stmt->execute([':username' => $username]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return new UserEntity(
            $data['id'],
            $data['user_name'],
            $data['password'],
            $data['mail'],
            $data['salt'],
            $data['token'] ?? null
        );
    }

    public function findByToken(string $token): ?UserEntity {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE token = :token");
        $stmt->execute([':token' => $token]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return new UserEntity(
            $data['id'],
            $data['user_name'],
            $data['password'],
            $data['mail'],
            $data['salt'],
            $data['token'] ?? null
        );
    }

    private function insert(UserEntity $user): bool {
        $stmt = $this->connection->prepare(
            "INSERT INTO users (user_name, password, mail, salt, token) 
             VALUES (:username, :password, :mail, :salt, :token)"
        );

        $success = $stmt->execute([
            ':username' => $user->getUserName(),
            ':password' => $user->getPassword(),
            ':mail' => $user->getMail(),
            ':salt' => $user->getSalt(),
            ':token' => $user->getToken()
        ]);

        if ($success) {
            $user->setId($this->connection->lastInsertId());
        }

        return $success;
    }

    private function update(UserEntity $user): bool {
        $stmt = $this->connection->prepare(
            "UPDATE users SET 
                user_name = :username, 
                password = :password, 
                mail = :mail, 
                salt = :salt, 
                token = :token 
             WHERE id = :id"
        );

        return $stmt->execute([
            ':id' => $user->getId(),
            ':username' => $user->getUserName(),
            ':password' => $user->getPassword(),
            ':mail' => $user->getMail(),
            ':salt' => $user->getSalt(),
            ':token' => $user->getToken()
        ]);
    }

}