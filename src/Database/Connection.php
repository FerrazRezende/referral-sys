<?php

namespace ReferralSystem\Database;

use Exception;
use PDO;
use PDOException;

class Connection
{
    private static ?PDO $instance = null;

    private static array $config = [];

    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }

        return self::$instance;
    }

    private static function connect(): void
    {
        try {
            $config = self::getConfig();

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            self::$instance = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                self::getPDOOptions()
            );

        } catch (PDOException $e) {
            throw new Exception('Error connecting to the database: '.$e->getMessage());
        }
    }

    private static function getConfig(): array
    {
        $defaultConfig = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_NAME'] ?? 'referral_system',
            'username' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? '',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
        ];

        return array_merge($defaultConfig, self::$config);
    }

    private static function getPDOOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
        ];
    }

    public static function testConnection(): bool
    {
        try {
            $pdo = self::getInstance();
            $pdo->query('SELECT 1');

            return true;
        } catch (Exception $e) {
            echo $e;

            return false;
        }
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $pdo = self::getInstance();

        if (empty($params)) {
            return $pdo->query($sql);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    public static function insert(string $sql, array $params = []): int
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $pdo->lastInsertId();
    }

    public static function execute(string $sql, array $params = []): int
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    public static function rollback(): bool
    {
        return self::getInstance()->rollBack();
    }

    public static function inTransaction(): bool
    {
        return self::getInstance()->inTransaction();
    }

    public static function close(): void
    {
        self::$instance = null;
    }

    public static function getInfo(): array
    {
        try {
            $pdo = self::getInstance();

            return [
                'connected' => true,
                'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
                'client_version' => $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION),
                'connection_status' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
                'server_info' => $pdo->getAttribute(PDO::ATTR_SERVER_INFO),
                'in_transaction' => $pdo->inTransaction(),
            ];
        } catch (Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
