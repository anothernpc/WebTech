<?php

abstract class Repository
{
    protected PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    abstract public function find(int $id): ?object;

    abstract public function findAll(): array;

    abstract public function save(object $entity): bool;


    abstract public function delete(int $id): bool;

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

    abstract protected function getTableName(): string;
}