<?php

namespace server\config;

class QueryPreparingConfig
{
    public static function updateLogin(): string
    {
        return 'UPDATE Users SET is_online = :is_online WHERE login = :login';
    }

    public static function selectLoginId(): string
    {
        return 'SELECT id as user_id FROM Users WHERE LOWER(login) = :login';
    }

    public static function selectOpponentLogin(): string
    {
        return 'SELECT login FROM Users WHERE id = :id';
    }

    public static function insertQueue(): string
    {
        return 'INSERT INTO Queues (user_id, status) VALUES (:user_id, 0)';
    }

    public static function updateQueue(): string
    {
        return 'UPDATE Queues SET status = :status WHERE user_id = :user_id';
    }

    public static function selectQueue(): string
    {
        return 'SELECT user_id FROM Queues WHERE status = :status AND user_id != :user_id LIMIT 1';
    }

    public static function selectQueueStatus(): string
    {
        return 'SELECT status FROM Queues WHERE status = :status AND user_id = :user_id LIMIT 1';
    }

    public static function deleteQueue(): string
    {
        return 'DELETE FROM Queues WHERE user_id = :user_id';
    }

    public static function insertGame(): string
    {
        return 'INSERT INTO Games (first_player, first_player_roll) VALUES (:first_player, :creatorRoll)';
    }

    public static function selectGameId(): string
    {
        return 'SELECT id as game_id FROM Games WHERE first_player = :first_player AND second_player IS NULL LIMIT 1';
    }

    public static function selectGameAsCreator(): string
    {
        return 'SELECT second_player FROM Games WHERE id = :id LIMIT 1';
    }

    public static function selectRoll(): string
    {
        return 'SELECT first_player_roll, second_player_roll FROM Games WHERE id = :id LIMIT 1';
    }

    public static function updateGame(): string
    {
        return 'UPDATE Games SET second_player = :userId, second_player_roll = :joinerRoll WHERE id = :gameId';
    }

    public static function deleteGame(): string
    {
        return 'DELETE FROM Games WHERE id = :gameId';
    }

    public static function deleteShips(): string
    {
        return 'DELETE FROM PrivateShips WHERE user_id = :user_id';
    }

    public static function insertShips(): string
    {
        return 'INSERT INTO PrivateShips (game_id, ship_type, direction, is_destroyed, start_coordinate, user_id) VALUES (:game_id, :ship_type, :direction, 0, :start_coordinate, :user_id)';
    }

    public static function selectShips(): string
    {
        return 'SELECT id FROM PrivateShips WHERE start_coordinate = :start_coordinate AND user_id = :user_id';
    }

    public static function deleteCoordinates(): string
    {
        return 'DELETE FROM PrivateCoordinates WHERE ship_id IN (SELECT id FROM PrivateShips WHERE user_id = :user_id)';
    }

    public static function insertCoordinates(): string
    {
        return 'INSERT INTO PrivateCoordinates (ship_id, coordinate, is_hit) VALUES (:ship_id, :coordinate, 0)';
    }


}