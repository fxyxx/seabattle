<?php

namespace Controllers;

use Exception;
use Models\PrepareModel;

require_once __DIR__ . '/../Models/PrepareModel.php';

class PrepareController
{
    private PrepareModel $prepareModel;

    public function __construct(PrepareModel $prepareModel)
    {
        $this->prepareModel = $prepareModel;
    }

    /**
     * Sets the user offline status.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response after setting the user offline.
     * @throws Exception
     */
    public function setUserOffline(array $postData): array
    {
        $response = [];
        $login = $postData['login'];

        $this->prepareModel->updateLogin($login, 0);

        $response['userLogin'] = $login;

        return $response;
    }

    /**
     * Creates a queue for a user.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response after creating the user queue.
     * @throws Exception
     */
    public function createQueue(array $postData): array
    {
        $response = [];

        $login = $postData['login'];
        $userId = $this->prepareModel->selectLoginId($login)[0]['user_id'];

        if (!!$userId) {
            $this->prepareModel->insertQueue($userId);
        }

        return $response;
    }

    /**
     * Starts a search for a game.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response after starting the game search.
     * @throws Exception
     */
    public function startSearch(array $postData): array
    {
        $login = $postData['login'];
        $shipsCoordinates = $postData['shipsCoordinates'];

        $userId = $this->prepareModel->selectLoginId($login)[0]['user_id'];
        $this->prepareModel->updateQueue($userId, 1);
        $opponentId = $this->prepareModel->selectQueue($userId, 1)[0]['user_id'];

        if (!$opponentId) {
            return $this->playerCreator($userId, $shipsCoordinates);
        }

        return $this->playerJoiner($opponentId, $userId, $shipsCoordinates);
    }

    /**
     * Creates a game and sets the user as the creator.
     *
     * @param string $userId User's ID.
     * @param array $shipsCoordinates Ships' coordinates for the game.
     *
     * @return array Response after creating a game.
     * @throws Exception
     */
    private function playerCreator(string $userId, array $shipsCoordinates): array
    {
        $response = [];
        $this->prepareModel->insertGame($userId, random_int(1, 1000));

        $gameId = $this->prepareModel->selectGameId($userId)[0]['game_id'];
        $isSecondJoin = $this->checkSecondPlayerJoin($gameId, $userId);

        if ($isSecondJoin) {
            $this->setShipsCoordinates($shipsCoordinates, $gameId, $userId);
            $this->prepareModel->updateQueue($userId, 2);

            $playersRolls = $this->prepareModel->selectRoll($gameId)[0];

            $response['gameId'] = $gameId;
            $response['myId'] = $userId;
            $response['opponentId'] = $isSecondJoin;
            $response['opponentLogin'] = $this->prepareModel->selectOpponentLogin($isSecondJoin)[0]['login'];
            $response['isYourTurn'] = $this->setTurn($playersRolls);
            $response['isGameStart'] = true;

            return $response;
        }

        $this->prepareModel->deleteGame($gameId);
        $this->prepareModel->updateQueue($userId, 0);

        return $response;
    }

    /**
     * Joins an existing game as the second player.
     *
     * @param string $opponentId Opponent's ID.
     * @param string $userId User's ID.
     * @param array $shipsCoordinates Ships' coordinates for the game.
     *
     * @return array Response after joining a game as the second player.
     * @throws Exception
     */
    private function playerJoiner(string $opponentId, string $userId, array $shipsCoordinates): array
    {
        $response = [];
        $gameId = $this->prepareModel->selectGameId($opponentId)[0]['game_id'];

        $this->prepareModel->updateGame($gameId, $userId, random_int(1, 1000));
        $this->prepareModel->updateQueue($userId, 2);
        $this->setShipsCoordinates($shipsCoordinates, $gameId, $userId);

        $playersRolls = $this->prepareModel->selectRoll($gameId)[0];

        $response['gameId'] = $gameId;
        $response['myId'] = $userId;
        $response['opponentId'] = $opponentId;
        $response['opponentLogin'] = $this->prepareModel->selectOpponentLogin($opponentId)[0]['login'];
        $response['isYourTurn'] = !$this->setTurn($playersRolls);
        $response['isGameStart'] = true;

        return $response;
    }

    /**
     * Sets the turn based on player rolls.
     *
     * @param array $playersRolls Roll values of the players.
     *
     * @return bool True if it's the user's turn, false otherwise.
     */
    private function setTurn(array $playersRolls): bool
    {
        if (intval($playersRolls['first_player_roll']) > intval($playersRolls['second_player_roll'])) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the second player joins the game.
     *
     * @param string $gameId Game ID.
     * @param string $userId User's ID.
     *
     * @return int|null Second player's ID or null.
     * @throws Exception
     */
    private function checkSecondPlayerJoin(string $gameId, string $userId)
    {
        $timeout = 90;

        while ($timeout > 0) {
            $result = $this->prepareModel->selectGameAsCreator($gameId)[0]['second_player'];
            $isStartGame = $this->prepareModel->selectQueueStatus($userId, 2)[0]['status'];

            if ($result !== null) {
                return $result;
            }
            if ($isStartGame === '2') {
                return $isStartGame;
            }

            sleep(1);
            $timeout--;
        }

        return 0;
    }

    /**
     * Sets ships coordinates for the game.
     *
     * @param array $shipsCoordinates Ships coordinates data.
     * @param string $gameId Game ID.
     * @param string $userId User's ID.
     * @throws Exception
     */
    private function setShipsCoordinates(array $shipsCoordinates, string $gameId, string $userId): void
    {
        $this->prepareModel->deleteCoordinates($userId);
        $this->prepareModel->deleteShips($userId);

        foreach ($shipsCoordinates as $coordDataKey => $coordDataValue) {
            $this->prepareModel->insertShips($coordDataKey, count($coordDataValue['coordinates']), $coordDataValue['direction'], $gameId, $userId);
        }

        foreach ($shipsCoordinates as $coordDataKey => $coordDataValue) {
            $result = $this->prepareModel->selectShips($coordDataKey, $userId)[0]['id'];

            if ($result) {
                $shipId = $result;
                $coordinates = $coordDataValue['coordinates'];

                foreach ($coordinates as $coordinate) {
                    $this->prepareModel->insertCoordinates($shipId, $coordinate);
                }
            }
        }
    }

    /**
     * Cancels the search for a game.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response after canceling the game search.
     * @throws Exception
     */
    public function cancelSearch(array $postData): array
    {
        $response = [];
        $login = $postData['login'];
        $userId = $this->prepareModel->selectLoginId($login)[0]['user_id'];
        $gameId = $this->prepareModel->selectGameId($userId)[0]['game_id'];

        $this->prepareModel->deleteGame($gameId);
        $this->prepareModel->updateQueue($userId, 0);

        $response['gameId'] = $gameId;
        $response['userId'] = $userId;

        return $response;
    }

    /**
     * Deletes the user queue.
     *
     * @param array $postData Data received from the request.
     * @throws Exception
     */
    public function deleteQueue(array $postData): void
    {
        $login = $postData['login'];
        $userId = $this->prepareModel->selectLoginId($login)[0]['user_id'];

        if (!!$userId) {
            $this->prepareModel->deleteQueue($userId);
        }
    }
}
