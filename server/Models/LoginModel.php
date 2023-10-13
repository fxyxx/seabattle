<?php

namespace Models;

require_once __DIR__ . '/DatabaseModel.php';
require_once __DIR__ . '/../config/QueryLoginConfig.php';

use server\config\QueryLoginConfig;
use Exception;
use PDOStatement;

class LoginModel
{
    private DatabaseModel $dbModel;

    public function __construct(DatabaseModel $dbModel)
    {
        $this->dbModel = $dbModel;
    }

    /**
     * Check if a nickname is already taken.
     *
     * @param string $nickname The nickname to check.
     * @param int $status The user's online status.
     *
     * @return bool True if the nickname is already taken, false otherwise.
     * @throws Exception
     */
    public function isNicknameTaken(string $nickname, int $status): bool
    {
        $sql = QueryLoginConfig::selectUserCount();
        $params = [':nickname' => $nickname, ':is_online' => $status];
        $result = $this->dbModel->select($sql, $params);

        if (intval($result[0]['userQuantity']) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Register a new login (user) with the provided login name.
     *
     * @param string $login The login name to register.
     *
     * @return PDOStatement The PDOStatement object representing the database insert operation.
     * @throws Exception
     */
    public function registerLogin(string $login): PDOStatement
    {
        $sql = QueryLoginConfig::insertUser();
        $params = ['login' => $login, 'is_online' => 1, 'last_update' => date("Y-m-d H:i:s")];
        return $this->dbModel->insert($sql, $params);
    }

    /**
     * Update the online status of a login (user).
     *
     * @param string $login The login name to update.
     *
     * @return PDOStatement The PDOStatement object representing the database update operation.
     * @throws Exception
     */
    public function updateLogin(string $login): PDOStatement
    {
        $sql = QueryLoginConfig::updateUserStatus();
        $params = [':login' => $login, ':is_online' => 1];
        return $this->dbModel->update($sql, $params);
    }

    /**
     * Check if a user with the specified ID is in a game with the provided status.
     *
     * @param string $myId The user's ID.
     * @param int $status The status to check.
     *
     * @return bool True if the user is in a game with the specified status, false otherwise.
     * @throws Exception
     */
    public function selectStatus(string $myId, int $status): bool
    {
        $sql = QueryLoginConfig::selectUserStatus();
        $params = [':user_id' => $myId, ':status' => $status];
        $result = $this->dbModel->select($sql, $params);

        if (intval($result[0]['isInGame']) !== 0) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the user's ID based on their login name.
     *
     * @param string $login The login name to search for.
     *
     * @return array An array containing the user's ID.
     * @throws Exception
     */
    public function selectLoginId(string $login): array
    {
        $sql = QueryLoginConfig::selectUserId();
        $params = [':login' => $login];
        return $this->dbModel->select($sql, $params);
    }
}


