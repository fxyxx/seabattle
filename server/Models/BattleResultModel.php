<?php

namespace Models;

require_once 'DatabaseModel.php';
require_once __DIR__ . '/../config/QueryBattleResultConfig.php';

use server\config\QueryBattleResultConfig;
use Models\DatabaseModel;
use Exception;
use PDOStatement;

class BattleResultModel
{
    private DatabaseModel $dbModel;

    public function __construct(DatabaseModel $dbModel)
    {
        $this->dbModel = $dbModel;
    }

    /**
     * Delete a user from the queue with a specified status.
     *
     * @param string $userId The user ID to delete from the queue.
     * @param int $status The status of the user in the queue (e.g., 1 for ready, 0 for not ready).
     *
     * @return PDOStatement The PDOStatement object representing the database delete operation.
     * @throws Exception If there's an error during the deletion process, an Exception is thrown with an error message.
     *
     * @throws Exception
     */
    public function deleteQueue(string $userId, int $status): PDOStatement
    {
        $sql = QueryBattleResultConfig::deleteQueue();
        $params = [':user_id' => $userId, ':status' => $status];

        try {
            return $this->dbModel->delete($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error deleting from queue: " . $e->getMessage());
        }
    }

    /**
     * Update the online status of a login (user).
     *
     * @param string $userId
     * @param int $status The updated online status (1 for online, 0 for offline).
     *
     * @return PDOStatement The PDOStatement object representing the database update operation.
     * @throws Exception
     */
    public function updateLogin(string $userId, int $status): PDOStatement
    {
        $sql = QueryBattleResultConfig::updateLogin();
        $params = [':id' => $userId, ':is_online' => $status];
        return $this->dbModel->update($sql, $params);
    }


}






