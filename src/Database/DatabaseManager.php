<?php

namespace ReferralSystem\Database;

use Exception;

class DatabaseManager
{
    public static function initialize(): void
    {
        if (! isset($_ENV['DB_HOST'])) {
            if (file_exists(__DIR__.'/../../.env')) {
                $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__.'/../..');
                $dotenv->load();
            }
        }

        Connection::configure([
            'host' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
            'database' => $_ENV['DB_NAME'],
            'username' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASS'],
            'charset' => $_ENV['DB_CHARSET'],
        ]);
    }

    public static function healthCheck(): array
    {
        try {
            $info = Connection::getInfo();

            if (! $info['connected']) {
                return [
                    'status' => 'error',
                    'message' => 'Unable to connect to the database',
                    'error' => $info['error'] ?? 'Unknown error',
                ];
            }

            $tables = self::checkTables();

            return [
                'status' => 'success',
                'message' => 'DB work correctly',
                'connection_info' => $info,
                'tables' => $tables,
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error checking database',
                'error' => $e->getMessage(),
            ];
        }
    }

    private static function checkTables(): array
    {
        $requiredTables = [
            'users',
            'binary_tree_structure',
            'referrals',
            'points_history',
            'referral_codes',
        ];

        $tables = [];

        foreach ($requiredTables as $table) {
            try {
                Connection::query("SELECT 1 FROM {$table} LIMIT 1");
                $tables[$table] = 'exists';
            } catch (Exception $e) {
                $tables[$table] = 'missing';
            }
        }

        return $tables;
    }

    public static function getStats(): array
    {
        try {
            return [
                'users_count' => (int) Connection::query('SELECT COUNT(*) FROM users')->fetchColumn(),
                'tree_nodes_count' => (int) Connection::query('SELECT COUNT(*) FROM binary_tree_structure')->fetchColumn(),
                'referrals_count' => (int) Connection::query('SELECT COUNT(*) FROM referrals')->fetchColumn(),
                'points_history_count' => (int) Connection::query('SELECT COUNT(*) FROM points_history')->fetchColumn(),
                'total_points' => (int) Connection::query('SELECT COALESCE(SUM(current_points), 0) FROM users')->fetchColumn(),
                'max_tree_level' => (int) Connection::query('SELECT COALESCE(MAX(level), 0) FROM binary_tree_structure')->fetchColumn(),
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }


}


