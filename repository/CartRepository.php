<?php
require_once __DIR__ . '/../model/CartEntity.php';
require_once __DIR__ . '/../repository/Repository.php';

class CartRepository extends Repository {
    protected function getTableName(): string {
        return 'carts';
    }

    public function find(int $id): ?CartEntity {
        $stmt = $this->connection->prepare("SELECT * FROM carts WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new CartEntity(
            $data['id'],
            $data['user_id'],
            $data['event_id'],
            $data['added_at'] ? new DateTime($data['added_at']) : new DateTime()
        );
    }

    public function findAll(): array {
        $stmt = $this->connection->query("SELECT * FROM carts");
        $items = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'id' => $data['id'] ?? null,
                'user_id' => (int)$data['user_id'],
                'event_id' => (int)$data['event_id'],
                'added_at' => $data['added_at']
            ];
        }

        return $items;
    }

    public function save(object $entity): bool {
        if (!$entity instanceof CartEntity) {
            return false;
        }

        if ($entity->getId() === null) {
            return $this->insert($entity);
        }
        return $this->update($entity);
    }

    public function findByUser(int $userId): array {
        $stmt = $this->connection->prepare("SELECT * FROM carts WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $items = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'id' => $data['id'],
                'user_id' => (int)$data['user_id'],
                'event_id' => (int)$data['event_id'],
                'added_at' => $data['added_at']
            ];
        }

        return $items;
    }

    public function findByUserAndEvent(int $userId, int $eventId): ?CartEntity {
        $stmt = $this->connection->prepare(
            "SELECT * FROM carts WHERE user_id = :user_id AND event_id = :event_id"
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':event_id' => $eventId
        ]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        return new CartEntity(
            $data['id'],
            $data['user_id'],
            $data['event_id'],
            $data['added_at'] ? new DateTime($data['added_at']) : new DateTime()
        );
    }

    private function insert(CartEntity $cart): bool {
        $stmt = $this->connection->prepare(
            "INSERT INTO carts (user_id, event_id, added_at) 
             VALUES (:user_id, :event_id, :added_at)"
        );

        $success = $stmt->execute([
            ':user_id' => $cart->getUserId(),
            ':event_id' => $cart->getEventId(),
            ':added_at' => $cart->getAddedAt()->format('Y-m-d H:i:s')
        ]);

        if ($success) {
            $cart->setId($this->connection->lastInsertId());
        }

        return $success;
    }

    private function update(CartEntity $cart): bool {
        $stmt = $this->connection->prepare(
            "UPDATE carts SET 
                user_id = :user_id,
                event_id = :event_id,
                added_at = :added_at
             WHERE id = :id"
        );

        return $stmt->execute([
            ':id' => $cart->getId(),
            ':user_id' => $cart->getUserId(),
            ':event_id' => $cart->getEventId(),
            ':added_at' => $cart->getAddedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->connection->prepare("DELETE FROM carts WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

}