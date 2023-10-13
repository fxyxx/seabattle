<?php

namespace Models;

require_once __DIR__ . '/DatabaseModel.php';
require_once __DIR__ . '/../config/QueryPreparingConfig.php';

use server\config\QueryPreparingConfig;
use Exception;
use PDOStatement;

class PrepareModel
{
    private DatabaseModel $dbModel;

    public function __construct(DatabaseModel $dbModel)
    {
        $this->dbModel = $dbModel;
    }

    /**
     * Update the online status of a login (user).
     *
     * @param string $login The login name to update.
     * @param int $status The updated online status (1 for online, 0 for offline).
     *
     * @return PDOStatement The PDOStatement object representing the database update operation.
     * @throws Exception
     */
    public function updateLogin(string $login, int $status): PDOStatement
    {
        $sql = QueryPreparingConfig::updateLogin();
        $params = [':login' => $login, ':is_online' => $status];
        return $this->dbModel->update($sql, $params);
    }

    /**
     * Select the ID of a user based on their login name.
     *
     * @param string $login The login name to search for.
     *
     * @return array An array containing the user's ID.
     * @throws Exception
     */
    public function selectLoginId(string $login): array
    {
        $sql = QueryPreparingConfig::selectLoginId();
        $params = [':login' => $login];
        return $this->dbModel->select($sql, $params);
    }

    /**
     * Select the login name of an opponent based on their user ID.
     *
     * @param string $userId The user ID of the opponent.
     *
     * @return array An array containing the opponent's login name.
     * @throws Exception
     */
    public function selectOpponentLogin(string $userId): array
    {
        $sql = QueryPreparingConfig::selectOpponentLogin();
        $params = [':id' => $userId];
        return $this->dbModel->select($sql, $params);
    }

    /**
     * Insert a user into the queue.
     *
     * @param string $userId The user ID to insert into the queue.
     *
     * @return PDOStatement The PDOStatement object representing the database insert operation.
     * @throws Exception
     */
    public function insertQueue(string $userId): PDOStatement
    {
        $sql = QueryPreparingConfig::insertQueue();
        $params = [':user_id' => $userId];
        return $this->dbModel->insert($sql, $params);
    }

    /**
     * Update the status of a user in the queue.
     *
     * @param string $userId The user ID to update.
     * @param int $status The updated status in the queue (e.g., 1 for ready, 0 for not ready).
     *
     * @return PDOStatement The PDOStatement object representing the database update operation.
     * @throws Exception
     */
    public function updateQueue(string $userId, int $status): PDOStatement
    {
        $sql = QueryPreparingConfig::updateQueue();
        $params = [':user_id' => $userId, ':status' => $status];
        return $this->dbModel->update($sql, $params);
    }

    /**
     * Select users from the queue with a specific status.
     *
     * @param string $userId The user ID to select.
     * @param int $status The status of the users in the queue (e.g., 1 for ready, 0 for not ready).
     *
     * @return array An array containing users from the queue with the specified status.
     * @throws Exception
     */
    public function selectQueue(string $userId, int $status): array
    {
        $sql = QueryPreparingConfig::selectQueue();
        $params = [':user_id' => $userId, ':status' => $status];
        return $this->dbModel->select($sql, $params);
    }

    /**
     * Select the queue status for a user.
     *
     * @param string $userId The user ID to select.
     * @param int $status The status of the user in the queue (e.g., 1 for ready, 0 for not ready).
     *
     * @return array An array containing the queue status for the user.
     * @throws Exception
     */
    public function selectQueueStatus(string $userId, int $status): array
    {
        $sql = QueryPreparingConfig::selectQueueStatus();
        $params = [':user_id' => $userId, ':status' => $status];
        return $this->dbModel->select($sql, $params);
    }

    /**
     * Delete a user from the queue.
     *
     * @param string $userId The user ID to delete from the queue.
     *
     * @return PDOStatement The PDOStatement object representing the database delete operation.
     * @throws Exception
     */
    public function deleteQueue(string $userId): PDOStatement
    {
        $sql = QueryPreparingConfig::deleteQueue();
        $params = [':user_id' => $userId];
        return $this->dbModel->delete($sql, $params);
    }

    /**
     * Insert a new game with the given user as the first player and creator.
     *
     * @param string $userId The user ID of the first player and creator of the game.
     * @param int $creatorRoll The roll value for the creator (e.g., 1 for player 1, 2 for player 2).
     *
     * @return PDOStatement The PDOStatement object representing the database insert operation.
     * @throws Exception
     */
    public function insertGame(string $userId, int $creatorRoll): PDOStatement
    {
        $sql = QueryPreparingConfig::insertGame();
        $params = [':first_player' => $userId, ':creatorRoll' => $creatorRoll];
        return $this->dbModel->insert($sql, $params);
    }

    /**
     * Select the game ID based on the opponent's user ID (second player).
     *
     * @param string $opponentId The user ID of the second player (opponent).
     *
     * @return array An array containing the game ID.
     * @throws Exception
     */
    public function selectGameId(string $opponentId): array
    {
        $sql = QueryPreparingConfig::selectGameId();
        $params = [':first_player' => $opponentId];
        return $this->dbModel->select($sql, $params);
    }

    /**
     * Select the game where the user is the creator (first player).
     *
     * @param string $gameId The game ID to select.
     *
     * @return array An array containing the game where the user is the creator.
     * @throws Exception
     */
    public function selectGameAsCreator(string $gameId): array
    {
        $sql = QueryPreparingConfig::selectGameAsCreator();
        $params = [':id' => $gameId];
        return $this->dbModel->select($sql, $params);
    }

    /**
     * Select the roll value for a game based on the game ID.
     *
     * @param string $gameId The game ID to select.
     *
     * @return array An array containing the roll value for the game.
     * @throws Exception
     */
    public function selectRoll(string $gameId): array
    {
        $sql = QueryPreparingConfig::selectRoll();
        $params = [':id' => $gameId];
        return $this->dbModel->select($sql, $params);
    }

    /**
     * Update the game with the user ID and joiner's roll value.
     *
     * @param string $gameId The game ID to update.
     * @param string $userId The user ID joining the game.
     * @param int $joinerRoll The roll value for the joiner (e.g., 1 for player 1, 2 for player 2).
     *
     * @return PDOStatement The PDOStatement object representing the database update operation.
     * @throws Exception
     */
    public function updateGame(string $gameId, string $userId, int $joinerRoll): PDOStatement
    {
        $sql = QueryPreparingConfig::updateGame();
        $params = [':userId' => $userId, ':joinerRoll' => $joinerRoll, ':gameId' => $gameId];
        return $this->dbModel->update($sql, $params);
    }

    /**
     * Delete a game based on the game ID.
     *
     * @param string $gameId The game ID to delete.
     *
     * @return PDOStatement The PDOStatement object representing the database delete operation.
     * @throws Exception
     */
    public function deleteGame(string $gameId): PDOStatement
    {
        $sql = QueryPreparingConfig::deleteGame();
        $params = [':gameId' => $gameId];
        return $this->dbModel->delete($sql, $params);
    }

    /**
     * Delete ships associated with a user.
     *
     * @param string $userId The user ID to delete ships for.
     *
     * @return PDOStatement The PDOStatement object representing the database delete operation.
     * @throws Exception
     */
    public function deleteShips(string $userId): PDOStatement
    {
        $sql = QueryPreparingConfig::deleteShips();
        $params = [':user_id' => $userId];
        return $this->dbModel->delete($sql, $params);
    }

    /**
     * Insert ship data into the database.
     *
     * @param string $coordKey The starting coordinate key for the ship.
     * @param int $shipType The type of ship.
     * @param string $shipDirection The direction of the ship (e.g., 'horizontal', 'vertical').
     * @param string $gameId The game ID associated with the ship.
     * @param string $userId The user ID associated with the ship.
     *
     * @return PDOStatement The PDOStatement object representing the database insert operation.
     * @throws Exception
     */
    public function insertShips(string $coordKey, int $shipType, string $shipDirection, string $gameId, string $userId): PDOStatement
    {
        $sql = QueryPreparingConfig::insertShips();
        $params = [
            ':game_id' => $gameId,
            ':ship_type' => $shipType,
            ':direction' => $shipDirection,
            ':start_coordinate' => strtolower($coordKey),
            ':user_id' => $userId,
        ];
        return $this->dbModel->insert($sql, $params);
    }

    /**
     * Select ship data based on the starting coordinate key and user ID.
     *
     * @param string $coordDataKey The starting coordinate key of the ship.
     * @param string $userId The user ID associated with the ship.
     *
     * @return array An array containing ship data.
     * @throws Exception
     */
    public function selectShips(string $coordDataKey, string $userId): array
    {
        $sql = QueryPreparingConfig::selectShips();
        $params = [':start_coordinate' => strtolower($coordDataKey), ':user_id' => $userId];
        return $this->dbModel->select($sql, $params);
    }

    /**
     * Delete coordinates associated with a user.
     *
     * @param string $userId The user ID to delete coordinates for.
     *
     * @return PDOStatement The PDOStatement object representing the database delete operation.
     * @throws Exception
     */
    public function deleteCoordinates(string $userId): PDOStatement
    {
        $sql = QueryPreparingConfig::deleteCoordinates();
        return $this->dbModel->delete($sql, [':user_id' => $userId]);
    }

    /**
     * Insert coordinates into the database associated with a ship.
     *
     * @param string $shipId The ship ID associated with the coordinates.
     * @param string $coordinate The coordinate to insert.
     *
     * @return PDOStatement The PDOStatement object representing the database insert operation.
     * @throws Exception
     */
    public function insertCoordinates(string $shipId, string $coordinate): PDOStatement
    {
        $sql = QueryPreparingConfig::insertCoordinates();
        $params = [':ship_id' => $shipId, ':coordinate' => strtolower($coordinate)];
        return $this->dbModel->insert($sql, $params);
    }
}
