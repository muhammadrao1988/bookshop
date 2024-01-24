<?php

namespace SalesDashboard\Service;

class DbService
{
    private $host = 'localhost';
    private $dbname = 'bookshop';
    private $username = 'root';
    private $password = 'muhammad';

    private $connection;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname}";

        try {
            $this->connection = new \PDO($dsn, $this->username, $this->password);
            // Set PDO to throw exceptions on error
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            // Handle connection errors
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function closeConnection()
    {
        $this->connection = null;
    }

}