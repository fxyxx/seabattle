<?php
namespace Controllers;
use Exception;
use Models\BattleModel;

require_once __DIR__ . '/../Models/PrepareModel.php';

class BattleController
{
    private BattleModel $battleModel;

    public function __construct(BattleModel $battleModel)
    {
        $this->battleModel = $battleModel;
    }

    /**
     * Sets a shot request.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response after setting the shot request.
     * @throws Exception
     */
    public function setShotRequest(array $postData): array
    {
        $response = [];
        $myId = $postData['myId'];
        $opponentId = $postData['opponentId'];
        $gameId = $postData['gameId'];
        $target = $postData['target'];

        $turnNumber = intval($this->battleModel->selectTurnNumberRequest($gameId, 1)[0]['turn_number']) + 1;
        $this->battleModel->insertSetShotRequest($myId, $gameId, $target, 1, null, $turnNumber);

        $response['myId'] = $myId;
        $response['opponentId'] = $opponentId;
        $response['gameId'] = $gameId;
        $response['target'] = $target;
        $response['turnNumber'] = $turnNumber;
        $response['request'] = 0;
        $response['isSuccess'] = true;

        return $response;
    }

    /**
     * Listens for a shot response.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response after listening for the shot response.
     * @throws Exception
     */
    public function listenerShotResponse(array $postData): array
    {
        $response = [];
        $myId = $postData['myId'];
        $opponentId = $postData['opponentId'];
        $gameId = $postData['gameId'];
        $target = $postData['target'];
        $turnNumber = $postData['turnNumber'];
        $request = $postData['request'];

        $shotType = $this->battleModel->selectShotResponse($gameId, $target, $turnNumber, $request)[0]['response'];

        if ($shotType > 20) {
            $response['startCoord'] =
                $this->battleModel->selectStartCoordinate($opponentId, $gameId, $target, 0)[0]['start_coord'];
            $response['shipLength'] =
                $this->battleModel->selectShipLength($opponentId, $gameId, $target, 0)[0]['ship_length'];
        }

        $tempResponse = $this->checkGameWinner($gameId, $myId, $opponentId);

        $response['iWinner'] = $tempResponse['iWinner'];
        $response['myAfkCount'] = $tempResponse['myAfkCount'];
        $response['opponentAfkCount'] = $tempResponse['opponentAfkCount'];
        $response['shotType'] = $shotType;
        $response['target'] = $target;
        $response['isYourTurn'] = intval($shotType) !== 0 && $shotType !== 'afk';

        return $response;
    }

    /**
     * Sets a shot response.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response after setting the shot response.
     * @throws Exception
     */
    public function setShotResponse(array $postData): array
    {
        $response = [];
        $myId = $postData['myId'];
        $opponentId = $postData['opponentId'];
        $gameId = $postData['gameId'];
        $target = $postData['target'];
        $turnNumber = $postData['turnNumber'];
        $startCoord = null;
        $length = null;

        $responseTarget = $this->battleModel->selectShotCoordinate($target, $myId)[0]['coordinate'];
        $shipId = $this->battleModel->selectShotShitId($target, $myId)[0]['ship_id'];
        $responseShot = $this->processShot($responseTarget, $myId, $shipId, $target);

        if ($responseShot > 20) {
            $startCoord = $this->battleModel->selectDestroyedShipStartCoordinate($shipId, 1, $myId)[0]['start_coordinate'];
            $length = $this->battleModel->selectDestroyedShipLength($shipId, 1, $myId)[0]['ship_type'];

            $response['startCoord'] = $startCoord;
            $response['shipLength'] = $length;
        }

        $this->battleModel->insertSetShotResponse($myId, $gameId, $target, 0, $responseShot, $turnNumber, $startCoord, $length);

        $tempResponse = $this->checkGameWinner($gameId, $myId, $opponentId);

        $response['iWinner'] = $tempResponse['iWinner'];
        $response['myAfkCount'] = $tempResponse['myAfkCount'];
        $response['opponentAfkCount'] = $tempResponse['opponentAfkCount'];
        $response['shotType'] = $responseShot;
        $response['target'] = $target;
        $response['isYourTurn'] = $responseShot === 0;

        return $response;
    }

    /**
     * Processes a shot.
     *
     * @param mixed $responseTarget Response target coordinate.
     * @param string $myId User's ID.
     * @param string|null $shipId Ship's ID.
     * @param string $target Shot target.
     *
     * @return int Shot response type.
     * @throws Exception
     */
    private function processShot($responseTarget, string $myId, ?string $shipId, string $target): int
    {
        if ($responseTarget !== null) {
            if ($target === 'afk') {
                return 0;
            }

            if ($this->battleModel->updateCoordinates($responseTarget, 1, $myId)->rowCount()) {
                if ($this->battleModel->updateShips($shipId, 1, $myId)->rowCount()) {
                    $direction =
                        $this->battleModel->selectDestroyedShipDirection($shipId, 1, $myId)[0]['direction'];

                    if ($direction === 'right') {
                        return 21;
                    } elseif ($direction === 'down') {
                        return 22;
                    } elseif ($direction === 'left') {
                        return 23;
                    } elseif ($direction === 'up') {
                        return 24;
                    }
                }

                return 1;
            }

            return 0;
        }

        return 0;
    }

    /**
     * Listens for a shot request.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response after listening for the shot request.
     * @throws Exception
     */
    public function listenerShotRequest(array $postData): array
    {
        $response = [];

        $myId = $postData['myId'];
        $opponentId = $postData['opponentId'];
        $gameId = $postData['gameId'];
        $turnNumber = intval($this->battleModel->selectTurnNumberResponse($gameId, 0)[0]['turn_number']) + 1;
        $target = $this->battleModel->selectShotRequest($gameId, $turnNumber, 1)[0]['target'];

        $response['myId'] = $myId;
        $response['opponentId'] = $opponentId;
        $response['gameId'] = $gameId;
        $response['target'] = $target;
        $response['turnNumber'] = $turnNumber;

        return $response;
    }

    /**
     * Checks for the game winner.
     *
     * @param string $gameId Game ID.
     * @param string $myId User's ID.
     * @param string $opponentId Opponent's ID.
     *
     * @return array Game winner information.
     * @throws Exception
     */
    private function checkGameWinner(string $gameId, string $myId, string $opponentId): array
    {
        $destroyedShipMyCount =
            $this->battleModel->selectIsWinner($gameId, $myId, 20)[0]['COUNT(*)'];
        $destroyedShipOpponentCount =
            $this->battleModel->selectIsWinner($gameId, $opponentId, 20)[0]['COUNT(*)'];

        $myAfkCount =
            $this->battleModel->selectAfkCount($gameId, $myId, 'afk', 1)[0]['COUNT(*)'];
        $opponentAfkCount =
            $this->battleModel->selectAfkCount($gameId, $opponentId, 'afk', 1)[0]['COUNT(*)'];

        if (intval($destroyedShipMyCount) >= 10 || intval($myAfkCount) >= 3) {
            $this->battleModel->updateGamesWinner($opponentId, $gameId);

            return ['iWinner' => $this->battleModel->selectWinner($gameId)[0]['winner'] === $myId,
                'myAfkCount' => $myAfkCount,
                'opponentAfkCount' => $opponentAfkCount];

        } elseif (intval($destroyedShipOpponentCount) >= 10 || intval($opponentAfkCount) >= 3) {
            $this->battleModel->updateGamesWinner($myId, $gameId);

            return ['iWinner' => $this->battleModel->selectWinner($gameId)[0]['winner'] === $myId,
                'myAfkCount' => $myAfkCount,
                'opponentAfkCount' => $opponentAfkCount];
        }

        return ['iWinner' => null, 'myAfkCount' => $myAfkCount, 'opponentAfkCount' => $opponentAfkCount];
    }

    /**
     * Sets the game winner.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response after setting the game winner and exiting the game.
     * @throws Exception
     */
    public function setGameWinnerExit(array $postData): array
    {
        $response = [];

        $opponentId = $postData['opponentId'];
        $gameId = $postData['gameId'];
        $myId = $postData['myId'];

        $this->battleModel->deleteQueue($myId, 2);
        $this->battleModel->updateUser($myId, 0);
        $this->battleModel->updateGamesWinner($opponentId, $gameId);

        $response['isStatusChanged'] = true;
        $response['isSetWinner'] = true;

        return $response;
    }

    /**
     * Sets the game winner and exits the game (back to the prepare menu).
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response after setting the game winner and exiting the game.
     * @throws Exception
     */
    public function setGameWinnerBack(array $postData): array
    {
        $response = [];

        $opponentId = $postData['opponentId'];
        $gameId = $postData['gameId'];
        $myId = $postData['myId'];

        $this->battleModel->deleteQueue($myId, 2);
        $this->battleModel->updateGamesWinner($opponentId, $gameId);

        $response['isStatusChanged'] = true;
        $response['isSetWinner'] = true;

        return $response;
    }

    /**
     * Sets the game winner when a player times out and exits the game.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response after setting the game winner and exiting the game.
     * @throws Exception
     */
    public function setTimeoutExitWinner(array $postData): array
    {
        $response = [];

        $myId = $postData['myId'];
        $gameId = $postData['gameId'];

        $this->battleModel->updateGamesWinner($myId, $gameId);

        $response['isSetWinner'] = true;

        return $response;
    }

    /**
     * Gets the game winner.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response with the game winner information.
     * @throws Exception
     */
    public function getGameWinnerExit(array $postData): array
    {
        $response = [];

        $gameId = $postData['gameId'];
        $myId = $postData['myId'];

        $winnerId = $this->battleModel->selectWinner($gameId)[0]['winner'];

        if ($myId === $winnerId) {
            $response['winnerId'] = $winnerId;
            $response['isSetWinner'] = true;
        }

        return $response;
    }

    /**
     * Sets the user's last update time.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response after setting the user's last update timestamp.
     * @throws Exception
     */
    public function setUserLastUpdate(array $postData): array
    {
        $response = [];
        $myId = $postData['myId'];

        $this->battleModel->updateUserLastUpdate($myId);
        $lastUpdateTimestamp = $this->battleModel->selectUserLastUpdate($myId)[0]['last_update'];
        $response['myLastUpdate'] = strtotime($lastUpdateTimestamp);

        return $response;
    }

    /**
     * Gets the opponent's last update time.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response with the opponent's last update timestamp.
     * @throws Exception
     */
    public function getUserLastUpdate(array $postData): array
    {
        $response = [];
        $opponentId = $postData['opponentId'];

        $opponentLastUpdateTimestamp = $this->battleModel->selectUserLastUpdate($opponentId)[0]['last_update'];
        $response['opponentLastUpdate'] = strtotime($opponentLastUpdateTimestamp);

        return $response;
    }

    /**
     * Updates the reconnect exit status for a user.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response after updating the reconnect exit status.
     * @throws Exception
     */
    public function updateReconnectExit(array $postData): array
    {
        $response = [];
        $myId = $postData['myId'];

        $this->battleModel->updateReconnectExit($myId, 0);

        $response['isUpdated'] = $postData;

        return $response;
    }
}
