<?php
error_reporting(0);
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/server/DatabaseConnection.php';
require_once __DIR__ . '/server/Models/DatabaseModel.php';
require_once __DIR__ . '/server/Logger.php';
require_once __DIR__ . '/server/RequestHandler.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Models\DatabaseModel;
use server\{DatabaseConnection, Logger, RequestHandler};

try {
    $dbConnection = DatabaseConnection::getInstance($_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
    $pdo = $dbConnection->getConnection();
    $dbModel = new DatabaseModel($pdo);
    $logger = Logger::getInstance();
    $requestHandler = new RequestHandler($dbModel, $logger);

    $postData = json_decode(file_get_contents('php://input'), true);
    $response = $requestHandler->handleRequest($postData);

    header('Content-Type: application/json');
    echo json_encode($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
