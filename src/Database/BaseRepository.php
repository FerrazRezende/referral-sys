<?php

namespace ReferralSystem\Database;

use Exception;
use PDO;

abstract class BaseRepository
{
    protected PDO $db;

    protected string $table;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    abstract protected function getTable(): string;

    public function findAll(): array
    {
        $sql = "SELECT * FROM {$this->getTable()} ORDER BY id ASC";

        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function findBy(array $conditions, string $orderBy = 'id ASC', ?int $limit = null): array
    {
        $sql = "SELECT * FROM {$this->getTable()}";

        if (! empty($conditions)) {
            $whereClause = [];
            foreach (array_keys($conditions) as $field) {
                $whereClause[] = "{$field} = :{$field}";
            }
            $sql .= ' WHERE '.implode(' AND ', $whereClause);
        }

        $sql .= " ORDER BY {$orderBy}";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);

        return $stmt->fetchAll();
    }

    public function findOneBy(array $conditions): ?array
    {
        $results = $this->findBy($conditions, 'id ASC', 1);

        return $results[0] ?? null;
    }

    public function create(array $data): int
    {
        $fields = array_keys($data);
        $placeholders = array_map(fn ($field) => ":{$field}", $fields);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->getTable(),
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $setClause = [];
        foreach (array_keys($data) as $field) {
            $setClause[] = "{$field} = :{$field}";
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE id = :id',
            $this->getTable(),
            implode(', ', $setClause)
        );

        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->getTable()} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->getTable()}";

        if (! empty($conditions)) {
            $whereClause = [];
            foreach (array_keys($conditions) as $field) {
                $whereClause[] = "{$field} = :{$field}";
            }
            $sql .= ' WHERE '.implode(' AND ', $whereClause);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);

        return (int) $stmt->fetchColumn();
    }

    public function exists(int $id): bool
    {
        return $this->count(['id' => $id]) > 0;
    }

    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    protected function queryScalar(string $sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollback(): bool
    {
        return $this->db->rollBack();
    }

    public function transaction(callable $callback)
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();

            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
