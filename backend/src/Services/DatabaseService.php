<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;
use RuntimeException;

class DatabaseService
{
    private PDO $pdo;

    public function __construct(
        string $host,
        string $database,
        string $username,
        string $password,
        int $port = 3306
    ) {
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Execute a SELECT query and return all results
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a SELECT query and return the first result
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE query
     */
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Execute an INSERT query and return the last inserted ID
     */
    public function insert(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Get the number of affected rows from the last query
     */
    public function rowCount(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Begin a database transaction
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit the current transaction
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Roll back the current transaction
     */
    public function rollback(): bool
    {
        return $this->pdo->rollback();
    }

    /**
     * Check if we're currently in a transaction
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * Execute a callback within a transaction
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Build a WHERE clause from an array of conditions
     */
    public function buildWhereClause(array $conditions, string $operator = 'AND'): array
    {
        if (empty($conditions)) {
            return ['', []];
        }

        $clauses = [];
        $params = [];
        $paramIndex = 0;

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                // Handle IN clauses
                $placeholders = [];
                foreach ($value as $item) {
                    $paramKey = "param_{$paramIndex}";
                    $placeholders[] = ":{$paramKey}";
                    $params[$paramKey] = $item;
                    $paramIndex++;
                }
                $clauses[] = "{$field} IN (" . implode(',', $placeholders) . ")";
            } elseif (strpos($field, ' ') !== false) {
                // Handle operators like 'price >='
                $paramKey = "param_{$paramIndex}";
                $clauses[] = "{$field} :{$paramKey}";
                $params[$paramKey] = $value;
                $paramIndex++;
            } else {
                // Handle simple equality
                $paramKey = "param_{$paramIndex}";
                $clauses[] = "{$field} = :{$paramKey}";
                $params[$paramKey] = $value;
                $paramIndex++;
            }
        }

        $whereClause = 'WHERE ' . implode(" {$operator} ", $clauses);
        return [$whereClause, $params];
    }

    /**
     * Build pagination LIMIT and OFFSET clause
     */
    public function buildPaginationClause(int $page = 1, int $limit = 20): string
    {
        $offset = ($page - 1) * $limit;
        return "LIMIT {$limit} OFFSET {$offset}";
    }

    /**
     * Build ORDER BY clause from array
     */
    public function buildOrderClause(array $orderBy): string
    {
        if (empty($orderBy)) {
            return '';
        }

        $clauses = [];
        foreach ($orderBy as $field => $direction) {
            $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            $clauses[] = "{$field} {$direction}";
        }

        return 'ORDER BY ' . implode(', ', $clauses);
    }
}
