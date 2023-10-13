<?php

namespace server;

use Exception;
use PDO;
use PDOException;

class DatabaseConnection
{
    private static ?self $instance = null;
    private PDO $pdo;

    /**
     * @throws Exception
     */
    private function __construct(string $host, string $dbname, string $username, string $password)
    {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Connection to db error: " . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public static function getInstance(string $host, string $dbname, string $username, string $password): self
    {
        if (self::$instance === null) {
            self::$instance = new self($host, $dbname, $username, $password);
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
