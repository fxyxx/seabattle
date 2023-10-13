<?php

namespace Models;

require_once 'DatabaseModel.php';
require_once __DIR__ . '/../config/QueryBattleConfig.php';

use server\config\QueryBattleConfig;
use Exception;
use PDOStatement;

class BattleModel
{
    private DatabaseModel $dbModel;

    public function __construct(DatabaseModel $dbModel)
    {
        $this->dbModel = $dbModel;
    }

    /**
     * Inserts a shot request record into the database.
     *
     * @param string $myId The ID of the player making the shot request.
     * @param string $gameId The ID of the game where the shot request is made.
     * @param string $target The target coordinate of the shot.
     * @param int $request The type of shot request (e.g., 1 hit, 2x kill).
     * @param mixed $response The response to the shot request (can be of any type).
     * @param int $turnNumber The turn number when the shot was made.
     *
     * @return PDOStatement The PDOStatement object representing the database insert operation.
     * @throws Exception If there's an error during the insertion process, an Exception is thrown with an error message.
     */
    public function insertSetShotRequest(string $myId, string $gameId, string $target, int $request, $response, int $turnNumber): PDOStatement
    {
        $sql = QueryBattleConfig::insertSetShotRequest();
        $params = [
            ':player_id' => $myId,
            ':game_id' => $gameId,
            ':target' => $target,
            ':request' => $request,
            ':response' => $response,
            ':turn_number' => $turnNumber,
            ':shot_time' => date("Y-m-d H:i:s"),
            ':start_coord' => null,
        ];

        try {
            return $this->dbModel->insert($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error inserting shot request: " . $e->getMessage());
        }
    }

    /**
     * Inserts a shot response record into the database.
     *
     * @param string $myId The ID of the player responding to the shot request.
     * @param string $gameId The ID of the game where the shot response is made.
     * @param string $target The target coordinate of the shot.
     * @param int $request The type of shot request (e.g., 1 hit, 2x kill).
     * @param int $response The response to the shot request.
     * @param string $turnNumber The turn number when the shot was made.
     * @param string|null $startCoord The starting coordinate of the shot if applicable.
     * @param string|null $length The length of the ship if applicable.
     *
     * @return PDOStatement The PDOStatement object representing the database insert operation.
     * @throws Exception If there's an error during the insertion process, an Exception is thrown with an error message.
     */
    public function insertSetShotResponse(string $myId, string $gameId, string $target, int $request, int $response,
                                          string $turnNumber, ?string $startCoord, ?string $length): PDOStatement
    {
        $sql = QueryBattleConfig::insertSetShotResponse();
        $params = [
            ':player_id' => $myId,
            ':game_id' => $gameId,
            ':target' => $target,
            ':request' => $request,
            ':response' => $response,
            ':turn_number' => $turnNumber,
            ':shot_time' => date("Y-m-d H:i:s"),
            ':start_coord' => $startCoord,
            ':ship_length' => $length,
        ];

        try {
            return $this->dbModel->insert($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error inserting shot response: " . $e->getMessage());
        }
    }

    /**
     * Selects the turn number associated with a shot request.
     *
     * @param string $gameId The ID of the game.
     * @param int $request The type of shot request (e.g., 1 hit, 2x kill).
     *
     * @return array An array containing the turn number associated with the shot request.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectTurnNumberRequest(string $gameId, int $request): array
    {
        $sql = QueryBattleConfig::selectTurnNumberRequest();
        $params = [':game_id' => $gameId, ':request' => $request];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting turn number for request: " . $e->getMessage());
        }
    }

    /**
     * Selects the turn number associated with a shot response.
     *
     * @param string $gameId The ID of the game.
     * @param int $request The type of shot request (e.g., 1 hit, 2x kill).
     *
     * @return array An array containing the turn number associated with the shot response.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectTurnNumberResponse(string $gameId, int $request): array
    {
        $sql = QueryBattleConfig::selectTurnNumberResponse();
        $params = [':game_id' => $gameId, ':request' => $request];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting turn number for response: " . $e->getMessage());
        }
    }

    /**
     * Selects the shot coordinate for a given target and user.
     *
     * @param string $target The target coordinate of the shot.
     * @param string $userId The ID of the user.
     *
     * @return array An array containing the shot coordinate.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectShotCoordinate(string $target, string $userId): array
    {
        $sql = QueryBattleConfig::selectShotCoordinate();
        $params = [':coordinate' => $target, ':user_id' => $userId];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting shot coordinate: " . $e->getMessage());
        }
    }

    /**
     * Selects the ship ID associated with a shot coordinate.
     *
     * @param string $target The target coordinate of the shot.
     * @param string $userId The ID of the user.
     *
     * @return array An array containing the ship ID associated with the shot coordinate.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectShotShitId(string $target, string $userId): array
    {
        $sql = QueryBattleConfig::selectShotShipId();
        $params = [':coordinate' => $target, ':user_id' => $userId];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting shot ship ID: " . $e->getMessage());
        }
    }

    /**
     * Selects the direction of a destroyed ship.
     *
     * @param string $shipId The ID of the ship.
     * @param int $isDestroyed Flag indicating whether the ship is destroyed.
     * @param string $userId The ID of the user.
     *
     * @return array An array containing the direction of the destroyed ship.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectDestroyedShipDirection(string $shipId, int $isDestroyed, string $userId): array
    {
        $sql = QueryBattleConfig::selectDestroyedShipDirection();
        $params = [':id' => $shipId, ':is_destroyed' => $isDestroyed, ':user_id' => $userId];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting destroyed ship direction: " . $e->getMessage());
        }
    }

    /**
     * Selects the length of a destroyed ship.
     *
     * @param string $shipId The ID of the ship.
     * @param int $isDestroyed Flag indicating whether the ship is destroyed.
     * @param string $userId The ID of the user.
     *
     * @return array An array containing the length of the destroyed ship.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectDestroyedShipLength(string $shipId, int $isDestroyed, string $userId): array
    {
        $sql = QueryBattleConfig::selectDestroyedShipLength();
        $params = [':id' => $shipId, ':is_destroyed' => $isDestroyed, ':user_id' => $userId];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting destroyed ship length: " . $e->getMessage());
        }
    }

    /**
     * Selects the start coordinate of a destroyed ship.
     *
     * @param string $shipId The ID of the ship.
     * @param int $isDestroyed Flag indicating whether the ship is destroyed.
     * @param string $userId The ID of the user.
     *
     * @return array An array containing the start coordinate of the destroyed ship.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectDestroyedShipStartCoordinate(string $shipId, int $isDestroyed, string $userId): array
    {
        $sql = QueryBattleConfig::selectDestroyedShipStartCoordinate();
        $params = [':id' => $shipId, ':is_destroyed' => $isDestroyed, ':user_id' => $userId];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting destroyed ship start coordinate: " . $e->getMessage());
        }
    }

    /**
     * Updates the hit status of a coordinate.
     *
     * @param string $coordinate The coordinate to update.
     * @param int $isHit Flag indicating whether the coordinate was hit.
     * @param string $userId The ID of the user.
     *
     * @return PDOStatement The PDOStatement object representing the database update operation.
     * @throws Exception If there's an error during the update process, an Exception is thrown with an error message.
     */
    public function updateCoordinates(string $coordinate, int $isHit, string $userId): PDOStatement
    {
        $sql = QueryBattleConfig::updateCoordinates();
        $params = [':coordinate' => $coordinate, ':is_hit' => $isHit, ':user_id' => $userId];

        try {
            return $this->dbModel->update($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error updating coordinates: " . $e->getMessage());
        }
    }

    /**
     * Updates the status of a ship (destroyed or not).
     *
     * @param string $shipId The ID of the ship.
     * @param int $isDestroyed Flag indicating whether the ship is destroyed.
     * @param string $userId The ID of the user.
     *
     * @return PDOStatement The PDOStatement object representing the database update operation.
     * @throws Exception If there's an error during the update process, an Exception is thrown with an error message.
     */
    public function updateShips(string $shipId, int $isDestroyed, string $userId): PDOStatement
    {
        $sql = QueryBattleConfig::updateShips();
        $params = [':id' => $shipId, ':is_destroyed' => $isDestroyed, ':user_id' => $userId];

        try {
            return $this->dbModel->update($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error updating ships: " . $e->getMessage());
        }
    }

    /**
     * Selects the shot response for a given game, target, turn number, and request type.
     *
     * @param string $gameId The ID of the game.
     * @param string $target The target coordinate of the shot.
     * @param string $turnNumber The turn number when the shot was made.
     * @param string $request The type of shot request (e.g., 1 for normal shot, 2 for special shot).
     *
     * @return array An array containing the shot response.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectShotResponse(string $gameId, string $target, string $turnNumber, string $request): array
    {
        $sql = QueryBattleConfig::selectShotResponse();
        $params = [
            ':game_id' => $gameId,
            ':target' => $target,
            ':turn_number' => $turnNumber,
            ':request' => $request,
        ];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting shot response: " . $e->getMessage());
        }
    }

    /**
     * Selects the shot request for a given game, turn number, and request type.
     *
     * @param string $gameId The ID of the game.
     * @param string $turnNumber The turn number when the shot was made.
     * @param int $request The type of shot request (e.g., 1 hit, 2x kill).
     *
     * @return array An array containing the shot request.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectShotRequest(string $gameId, string $turnNumber, int $request): array
    {
        $sql = QueryBattleConfig::selectShotRequest();
        $params = [':game_id' => $gameId, ':turn_number' => $turnNumber, ':request' => $request];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting shot request: " . $e->getMessage());
        }
    }

    /**
     * Selects the winner of a game based on the game ID, player ID, and response type.
     *
     * @param string $gameId The ID of the game.
     * @param string $playerId The ID of the player.
     * @param int $response The response type (e.g., 1 for win, 2 for loss).
     *
     * @return array An array containing the winner information.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectIsWinner(string $gameId, string $playerId, int $response): array
    {
        $sql = QueryBattleConfig::selectIsWinner();
        $params = [':game_id' => $gameId, ':player_id' => $playerId, ':response' => $response];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting winner: " . $e->getMessage());
        }
    }

    /**
     * Updates the winner of a game based on the game ID.
     *
     * @param string $userId The ID of the user who won the game.
     * @param string $gameId The ID of the game.
     *
     * @return PDOStatement The PDOStatement object representing the database update operation.
     * @throws Exception If there's an error during the update process, an Exception is thrown with an error message.
     */
    public function updateGamesWinner(string $userId, string $gameId): PDOStatement
    {
        $sql = QueryBattleConfig::updateGamesWinner();
        $params = [':id' => $gameId, ':winner' => $userId];

        try {
            return $this->dbModel->update($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error updating game winner: " . $e->getMessage());
        }
    }

    /**
     * Selects the winner of a game based on the game ID.
     *
     * @param string $gameId The ID of the game.
     *
     * @return array An array containing the winner information.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectWinner(string $gameId): array
    {
        $sql = QueryBattleConfig::selectWinner();
        $params = [':game_id' => $gameId];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting winner: " . $e->getMessage());
        }
    }

    /**
     * Selects the start coordinate for a shot request.
     *
     * @param string $opponentId The ID of the opponent player.
     * @param string $gameId The ID of the game.
     * @param string $target The target coordinate of the shot.
     * @param int $request The type of shot request (e.g., 1 hit, 2x kill).
     *
     * @return array An array containing the start coordinate for the shot request.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectStartCoordinate(string $opponentId, string $gameId, string $target, int $request): array
    {
        $sql = QueryBattleConfig::selectStartCoordinate();
        $params = [':player_id' => $opponentId, ':game_id' => $gameId, ':target' => $target, ':request' => $request];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting start coordinate: " . $e->getMessage());
        }
    }

    /**
     * Selects the length of a ship for a shot request.
     *
     * @param string $opponentId The ID of the opponent player.
     * @param string $gameId The ID of the game.
     * @param string $target The target coordinate of the shot.
     * @param int $request The type of shot request (e.g., 1 hit, 2x kill).
     *
     * @return array An array containing the length of the ship for the shot request.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectShipLength(string $opponentId, string $gameId, string $target, int $request): array
    {
        $sql = QueryBattleConfig::selectShipLength();
        $params = [':player_id' => $opponentId, ':game_id' => $gameId, ':target' => $target, ':request' => $request];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting ship length: " . $e->getMessage());
        }
    }

    /**
     * Selects the AFK count for a shot request.
     *
     * @param string $gameId The ID of the game.
     * @param string $playerId The ID of the player.
     * @param string $target The target coordinate of the shot.
     * @param int $request The type of shot request (e.g., 1 hit, 2x kill).
     *
     * @return array An array containing the AFK count for the shot request.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectAfkCount(string $gameId, string $playerId, string $target, int $request): array
    {
        $sql = QueryBattleConfig::selectAfkCount();
        $params = [
            ':game_id' => $gameId,
            ':player_id' => $playerId,
            ':target' => $target,
            ':request' => $request,
        ];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting AFK count: " . $e->getMessage());
        }
    }

    /**
     * Updates the last update timestamp for a user.
     *
     * @param string $userId The ID of the user.
     *
     * @return PDOStatement The PDOStatement object representing the database update operation.
     * @throws Exception If there's an error during the update process, an Exception is thrown with an error message.
     */
    public function updateUserLastUpdate(string $userId): PDOStatement
    {
        $sql = QueryBattleConfig::updateUserLastUpdate();
        $params = [':id' => $userId, ':last_update' => date("Y-m-d H:i:s")];

        try {
            return $this->dbModel->update($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error updating user last update: " . $e->getMessage());
        }
    }

    /**
     * Selects the last update timestamp for a user.
     *
     * @param string $opponentId The ID of the opponent player.
     *
     * @return array An array containing the last update timestamp for the user.
     * @throws Exception If there's an error during the selection process, an Exception is thrown with an error message.
     */
    public function selectUserLastUpdate(string $opponentId): array
    {
        $sql = QueryBattleConfig::selectUserLastUpdate();
        $params = [':id' => $opponentId];

        try {
            return $this->dbModel->select($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error selecting user last update: " . $e->getMessage());
        }
    }

    /**
     * Deletes records from the queue based on user ID and status.
     *
     * @param string $userId The ID of the user.
     * @param int $status The status of the queue records to delete.
     *
     * @return PDOStatement The PDOStatement object representing the database delete operation.
     * @throws Exception If there's an error during the deletion process, an Exception is thrown with an error message.
     */
    public function deleteQueue(string $userId, int $status): PDOStatement
    {
        $sql = QueryBattleConfig::deleteQueue();
        $params = [':user_id' => $userId, ':status' => $status];

        try {
            return $this->dbModel->delete($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error deleting from queue: " . $e->getMessage());
        }
    }

    /**
     * Updates the online status of a user.
     *
     * @param string $userId The ID of the user.
     * @param int $isOnline Flag indicating whether the user is online (1 for online, 0 for offline).
     *
     * @return PDOStatement The PDOStatement object representing the database update operation.
     * @throws Exception If there's an error during the update process, an Exception is thrown with an error message.
     */
    public function updateUser(string $userId, int $isOnline): PDOStatement
    {
        $sql = QueryBattleConfig::updateUser();
        $params = [':id' => $userId, ':is_online' => $isOnline];

        try {
            return $this->dbModel->update($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error updating user: " . $e->getMessage());
        }
    }

    /**
     * Updates the reconnect/exit status of a user.
     *
     * @param string $userId The ID of the user.
     * @param int $isOnline Flag indicating whether the user is online (1 for online, 0 for offline).
     *
     * @return PDOStatement The PDOStatement object representing the database update operation.
     * @throws Exception If there's an error during the update process, an Exception is thrown with an error message.
     */
    public function updateReconnectExit(string $userId, int $isOnline): PDOStatement
    {
        $sql = QueryBattleConfig::updateReconnectExit();
        $params = [':id' => $userId, ':is_online' => $isOnline];

        try {
            return $this->dbModel->update($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error updating reconnect/exit: " . $e->getMessage());
        }
    }
}


