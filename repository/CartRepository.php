<?php
require_once __DIR__ . '/../model/CartEntity.php';
require_once __DIR__ . '/../repository/Repository.php';
class CartRepository extends Repository {
    public function find(int $id): ?CartEntity {
        $stmt = $this->connection->prepare("SELECT * FROM cart WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return new CartEntity(
            $data['id'] ?? null,
            $data['title'],
            $data['date'],
            (int)$data['price'],
            (int)$data['quantity'],
            (int)$data['subtotal']

        );
    }

    public function findAll(): array {
        $stmt = $this->connection->query("SELECT * FROM cart");
        $items = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'id' => $data['id'] ?? null,
                'title' => $data['title'],
                'date' => $data['date'],
                'price' => (int)$data['price'],
                'quantity' => (int)$data['quantity'],
                'subtotal' => (int)$data['subtotal'],
            ];
        }

        return $items;
    }

    public function save(object $entity): bool {
        if (!$entity instanceof CartEntity) {
            return false;
        }

        return $entity->getId() === null
            ? $this->insert($entity)
            : $this->update($entity);
    }

    public function delete(int $id): bool {
        $stmt = $this->connection->prepare("DELETE FROM cart WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Специфичные методы для Cart
    public function findByTitle(string $title): array {
        $stmt = $this->connection->prepare("SELECT * FROM cart WHERE title LIKE :title");
        $stmt->execute([':title' => "%$title%"]);
        $items = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'id' => $data['id'] ?? null,
                'title' => $data['title'],
                'date' => $data['date'],
                'price' => (int)$data['price'],
                'quantity' => (int)$data['quantity'],
                'subtotal' => (int)$data['subtotal'],
            ];
        }

        return $items;
    }

    public function findByDateRange(string $startDate, string $endDate): array {
        $stmt = $this->connection->prepare(
            "SELECT * FROM cart WHERE date BETWEEN :start_date AND :end_date"
        );
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        $items = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'id' => $data['id'] ?? null,
                'title' => $data['title'],
                'date' => $data['date'],
                'price' => (int)$data['price'],
                'quantity' => (int)$data['quantity'],
                'subtotal' => (int)$data['subtotal'],
            ];
        }

        return $items;
    }

    private function insert(CartEntity $cart): bool {
        $stmt = $this->connection->prepare(
            "INSERT INTO cart (title, date, price, quantity, subtotal) 
             VALUES (:title, :date, :price, :quantity, :subtotal)"
        );

        $success = $stmt->execute([
            ':title' => $cart->getTitle(),
            ':date' => $cart->getDate(),
            ':price' => $cart->getPrice(),
            ':quantity' => $cart->getQuantity(),
            ':subtotal' => $cart->getSubtotal()
        ]);

        if ($success) {
            $cart->setId($this->connection->lastInsertId());
        }

        return $success;
    }

    private function update(CartEntity $cart): bool {
        $stmt = $this->connection->prepare(
            "UPDATE cart SET 
                title = :title,
                date = :date,
                price = :price,
                quantity = :quantity,
                subtotal = :subtotal
             WHERE id = :id"
        );

        return $stmt->execute([
            ':id' => $cart->getId(),
            ':title' => $cart->getTitle(),
            ':date' => $cart->getDate(),
            ':price' => $cart->getPrice(),
            ':quantity' => $cart->getQuantity(),
            ':subtotal' => $cart->getSubtotal()
        ]);
    }

    protected function getTableName(): string {
        return 'cart';
    }
}