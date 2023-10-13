<?php

namespace server;

require_once __DIR__ . '/Models/LoginModel.php';
require_once __DIR__ . '/Controllers/LoginController.php';
require_once __DIR__ . '/Models/PrepareModel.php';
require_once __DIR__ . '/Controllers/PrepareController.php';
require_once __DIR__ . '/Models/BattleModel.php';
require_once __DIR__ . '/Controllers/BattleController.php';
require_once __DIR__ . '/Models/BattleResultModel.php';
require_once __DIR__ . '/Controllers/BattleResultController.php';
require_once __DIR__ . '/config/PackageTypeListConfig.php';

use Controllers\{LoginController, PrepareController, BattleController, BattleResultController};
use Models\{LoginModel, PrepareModel, BattleModel, BattleResultModel};
use server\config\PackageTypeListConfig;

class RequestHandler
{
    private $logger;
    private $dbModel;

    public function __construct($dbModel, $logger)
    {
        $this->dbModel = $dbModel;
        $this->logger = $logger;
    }

    /**
     * Adds a timestamp to the response.
     */
    private function addTimestamp(array &$response)
    {
        $response['timeStamp'] = time();
    }

    /**
     * Handles incoming requests.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Result of processing the request.
     */
    public function handleRequest(array $postData): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->logger->error('Invalid request method.');
            return ['error' => 'Invalid request method'];
        }

        $packageInitiator = $postData['packageInitiator'];
        $packageType = $postData['packageType'];

        switch ($packageInitiator) {
            case 'Login':
                $this->logger->info('Package initiator: Login.');
                $controller = new LoginController(new LoginModel($this->dbModel));
                return $this->handleLoginRequest($controller, $postData);

            case 'Prepare':
                $this->logger->info('Package initiator: Prepare.');
                $controller = new PrepareController(new PrepareModel($this->dbModel));
                return $this->handlePrepareRequest($controller, $packageType, $postData);

            case 'Battle':
                $this->logger->info('Package initiator: Battle.');
                $controller = new BattleController(new BattleModel($this->dbModel));
                return $this->handleBattleRequest($controller, $packageType, $postData);

            case 'BattleResult':
                $this->logger->info('Package initiator: BattleResult.');
                $controller = new BattleResultController(new BattleResultModel($this->dbModel));
                return $this->handleBattleResultRequest($controller, $packageType, $postData);

            default:
                $this->logger->error('Invalid package initiator.');
                return ['error' => 'Invalid package initiator'];
        }
    }

    /**
     * Handles Login package requests.
     *
     * @param mixed $controller The LoginController instance.
     * @param array $postData Data received from the request.
     *
     * @return array Result of processing the Login request.
     */
    private function handleLoginRequest($controller, array $postData): array
    {
        if ($postData['packageType'] === "User authentication") {
            $response = $controller->handleLoginRequest($postData);
            $response['packageType'] = "User authentication";
            $this->logger->info('User authentication request.');
        } else {
            $response['error'] = 'Invalid login package type';
            $this->logger->error('Invalid login package type.');
        }
        $this->addTimestamp($response);

        return $response;
    }

    /** Handles Prepare package requests.
     *
     * @param mixed $controller The PrepareController instance.
     * @param string $packageType The type of Prepare package.
     * @param array $postData Data received from the request.
     *
     * @return array Result of processing the Prepare request.
     */
    private function handlePrepareRequest($controller, string $packageType, array $postData): array
    {
        switch ($packageType) {
            case "User exit":
                $response['userOffline'] = $controller->setUserOffline($postData);
                $response['userDeleteQueue'] = $controller->deleteQueue($postData);
                $response['packageType'] = "User exit";
                $response['isUserLeave'] = true;
                $this->logger->info('User exit request.');
                break;

            case "Add user in queue":
                $response = $controller->createQueue($postData);
                $this->logger->info('Add user in queue request.');
                break;

            case "User start search the game":
                $response = $controller->startSearch($postData);
                $this->logger->info('User start search the game request.');
                break;

            case "Queue exit":
                $response = $controller->cancelSearch($postData);
                $this->logger->info('Queue exit request.');
                break;

            default:
                $response['error'] = 'Invalid prepare package type';
                $this->logger->error('Invalid prepare package type.');
        }
        $this->addTimestamp($response);

        return $response;
    }

    /**
     * Handles Battle package requests.
     *
     * @param mixed $controller The BattleController instance.
     * @param string $packageType The type of Battle package.
     * @param array $postData Data received from the request.
     *
     * @return array Result of processing the Battle request.
     */
    private function handleBattleRequest($controller, string $packageType, array $postData): array
    {
        $response = [];
        $packageTypeList = PackageTypeListConfig::battlePackage();

        if (isset($packageTypeList[$packageType])) {
            $methodName = $packageTypeList[$packageType];

            if (method_exists($controller, $methodName)) {
                $response = call_user_func([$controller, $methodName], $postData);
                $this->logger->info($methodName);
            } else {
                $response['error'] = 'Invalid battle package type';
                $this->logger->error('Invalid battle package type.');
            }
        } else {
            $response['error'] = 'Invalid battle package type';
            $this->logger->error('Invalid battle package type.');
        }

        $this->addTimestamp($response);

        return $response;
    }

    /**
     * Handles BattleResult package requests.
     *
     * @param mixed $controller The BattleResultController instance.
     * @param string $packageType The type of BattleResult package.
     * @param array $postData Data received from the request.
     *
     * @return array Result of processing the BattleResult request.
     */
    private function handleBattleResultRequest($controller, string $packageType, array $postData): array
    {
        $response = [];
        $this->addTimestamp($response);

        if ($packageType === "User play again") {
            $this->logger->info('User play again.');
            return $controller->setUserPlayAgain($postData);

        } elseif ($packageType === "User exit") {
            $this->logger->info('User exit.');

            $controller->setUserPlayAgain($postData);
            return $controller->setUserOffline($postData);
        }


        return $response;
    }
}
