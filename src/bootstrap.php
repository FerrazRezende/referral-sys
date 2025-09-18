<?php

require_once __DIR__.'/../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use ReferralSystem\Database\DatabaseManager;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();


error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] ?? '1');
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/../'.($_ENV['LOG_FILE'] ?? 'logs/app.log'));

date_default_timezone_set('America/Sao_Paulo');

header('Content-Type: text/html; charset=UTF-8');


try {
    DatabaseManager::initialize();

    $healthCheck = DatabaseManager::healthCheck();

    if ($healthCheck['status'] !== 'success') {
        throw new Exception('Banco não está funcionando: '.$healthCheck['message']);
    }

} catch (Exception $e) {
    if ($_ENV['APP_DEBUG'] ?? false) {
        exit('Erro na inicialização: '.$e->getMessage());
    }

    error_log('Bootstrap Error: '.$e->getMessage());
    exit('Sistema temporariamente indisponível. Tente novamente em alguns minutos.');
}

if (! function_exists('dd')) {
    function dd(...$vars)
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        exit();
    }
}


if (! function_exists('logger')) {
    function logger(string $message, string $level = 'info'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}".PHP_EOL;
        file_put_contents(__DIR__.'/../'.($_ENV['LOG_FILE'] ?? 'logs/app.log'), $logMessage, FILE_APPEND);
    }
}

if (! function_exists('json_response')) {
    function json_response(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, [
    'debug' => $_ENV['APP_DEBUG'] ?? false,
]);
if ($_ENV['APP_DEBUG'] ?? false) {
    $twig->addExtension(new \Twig\Extension\DebugExtension());
}
