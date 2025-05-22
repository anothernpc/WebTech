<?php

require_once __DIR__ . '/../model/EventEntity.php';
require_once __DIR__ . '/../repository/Repository.php';

class EventRepository extends Repository {
    public function find(int $id): ?EventEntity {
        $stmt = $this->connection->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new EventEntity(
            $data['id'] ?? null,
            $data['title'],
            $data['description'],
            $data['date'],
            (int)$data['price'],
            $data['image'] ?? null
        );
    }

    public function findAll(): array {
        $stmt = $this->connection->query("SELECT * FROM events");
        $events = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = [
                'id' => $data['id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'],
                'date' => $data['date'],
                'price' => (int)$data['price'],
                'image' => $data['image'] ?? null
            ];
        }

        return $events;
    }

    public function save(object $entity): bool {
        if (!$entity instanceof EventEntity) {
            return false;
        }

        return $entity->getId() === null
            ? $this->insert($entity)
            : $this->update($entity);
    }

    public function delete(int $id): bool {
        $stmt = $this->connection->prepare("DELETE FROM events WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function findByTitle(string $title): array {
        $stmt = $this->connection->prepare("SELECT * FROM events WHERE title LIKE :title");
        $stmt->execute([':title' => "%$title%"]);
        $events = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = [
                'id' => $data['id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'],
                'date' => $data['date'],
                'price' => (int)$data['price'],
                'image' => $data['image'] ?? null
            ];
        }

        return $events;
    }

    public function findUpcomingEvents(int $limit = 10): array {
        $currentDate = date('Y-m-d');
        $stmt = $this->connection->prepare(
            "SELECT * FROM events WHERE date >= :current_date ORDER BY date ASC LIMIT :limit"
        );
        $stmt->bindValue(':current_date', $currentDate, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $events = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = [
                'id' => $data['id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'],
                'date' => $data['date'],
                'price' => (int)$data['price'],
                'image' => $data['image'] ?? null
            ];
        }

        return $events;
    }

    private function insert(EventEntity $event): bool {
        $stmt = $this->connection->prepare(
            "INSERT INTO events (title, description, date, price, image) 
             VALUES (:title, :description, :date, :price, :image)"
        );

        $success = $stmt->execute([
            ':title' => $event->getTitle(),
            ':description' => $event->getDescription(),
            ':date' => $event->getDate(),
            ':price' => $event->getPrice(),
            ':image' => $event->getImage()
        ]);

        if ($success) {
            $event->setId($this->connection->lastInsertId());
        }

        return $success;
    }

    private function update(EventEntity $event): bool {
        $stmt = $this->connection->prepare(
            "UPDATE events SET 
                title = :title,
                description = :description,
                date = :date,
                price = :price,
                image = :image
             WHERE id = :id"
        );

        return $stmt->execute([
            ':id' => $event->getId(),
            ':title' => $event->getTitle(),
            ':description' => $event->getDescription(),
            ':date' => $event->getDate(),
            ':price' => $event->getPrice(),
            ':image' => $event->getImage()
        ]);
    }

    protected function getTableName(): string {
        return 'events';
    }
}