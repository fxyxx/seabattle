<?php
namespace Models;
use Exception;
use PDO;
use PDOException;
use PDOStatement;

class DatabaseModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @throws Exception
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query error: " . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @throws Exception
     */
    public function insert(string $sql, array $data): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Insert error: " . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function update(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Update error: " . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function delete(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Delete error: " . $e->getMessage());
        }
    }
}


