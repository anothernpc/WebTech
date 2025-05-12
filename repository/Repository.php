<?php

abstract class Repository
{
    protected PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Найти сущность по ID
     */
    abstract public function find(int $id): ?object;

    /**
     * Найти все сущности
     */
    abstract public function findAll(): array;

    /**
     * Сохранить сущность (вставка или обновление)
     */
    abstract public function save(object $entity): bool;

    /**
     * Удалить сущность
     */
    abstract public function delete(int $id): bool;

    /**
     * Найти по критериям
     */
    public function findBy(array $criteria): array
    {
        $query = "SELECT * FROM " . $this->getTableName() . " WHERE ";
        $conditions = [];
        $params = [];

        foreach ($criteria as $field => $value) {
            $conditions[] = "$field = :$field";
            $params[":$field"] = $value;
        }

        $query .= implode(" AND ", $conditions);

        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Получить имя таблицы (должен быть реализован в дочерних классах)
     */
    abstract protected function getTableName(): string;
}