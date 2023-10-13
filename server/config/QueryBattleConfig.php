<?php

namespace server\config;

class QueryBattleConfig
{
    public static function insertSetShotRequest(): string
    {
        return "INSERT INTO Shots (player_id, game_id, target, request, response, turn_number, shot_time, start_coord) 
                VALUES (:player_id, :game_id, :target, :request, :response, :turn_number, :shot_time, :start_coord)";
    }

    public static function insertSetShotResponse(): string
    {
        return "INSERT INTO Shots (player_id, game_id, target, request, response, turn_number, shot_time, start_coord, ship_length) 
                VALUES (:player_id, :game_id, :target, :request, :response, :turn_number, :shot_time, :start_coord, :ship_length)";
    }

    public static function selectTurnNumberRequest(): string
    {
        return "SELECT turn_number FROM Shots WHERE game_id = :game_id AND request = :request ORDER BY id DESC LIMIT 1";
    }

    public static function selectTurnNumberResponse(): string
    {
        return "SELECT turn_number FROM Shots WHERE game_id = :game_id AND request = :request ORDER BY id DESC LIMIT 1";
    }

    public static function selectShotCoordinate(): string
    {
        return "SELECT cn.coordinate FROM PrivateCoordinates cn 
                WHERE cn.coordinate = :coordinate 
                AND EXISTS (SELECT 1 FROM PrivateShips sn WHERE sn.user_id = :user_id AND sn.id = cn.ship_id)";
    }

    public static function selectShotShipId(): string
    {
        return "SELECT s.id AS ship_id FROM PrivateShips AS s 
                INNER JOIN PrivateCoordinates AS c ON s.id = c.ship_id 
                WHERE c.coordinate = :coordinate AND s.user_id = :user_id";
    }

    public static function selectDestroyedShipDirection(): string
    {
        return "SELECT direction FROM PrivateShips WHERE id = :id AND is_destroyed = :is_destroyed AND user_id = :user_id";
    }

    public static function selectDestroyedShipLength(): string
    {
        return "SELECT ship_type FROM PrivateShips WHERE id = :id AND is_destroyed = :is_destroyed AND user_id = :user_id";
    }

    public static function selectDestroyedShipStartCoordinate(): string
    {
        return "SELECT start_coordinate FROM PrivateShips WHERE id = :id AND is_destroyed = :is_destroyed AND user_id = :user_id";
    }

    public static function updateCoordinates(): string
    {
        return "UPDATE PrivateCoordinates 
                SET is_hit = :is_hit 
                WHERE ship_id IN (SELECT s.id FROM PrivateShips s WHERE s.user_id = :user_id) 
                AND coordinate = :coordinate";
    }

    public static function updateShips(): string
    {
        return "UPDATE PrivateShips AS s 
                SET is_destroyed = :is_destroyed 
                WHERE user_id = :user_id 
                AND id = :id 
                AND NOT EXISTS (SELECT 1 FROM PrivateCoordinates AS c WHERE c.ship_id = s.id AND c.is_hit = 0)";
    }

    public static function selectShotResponse(): string
    {
        return "SELECT response FROM Shots 
                WHERE game_id = :game_id 
                AND target = :target 
                AND turn_number = :turn_number 
                AND request = :request 
                LIMIT 1";
    }

    public static function selectShotRequest(): string
    {
        return "SELECT target FROM Shots 
                WHERE game_id = :game_id 
                AND turn_number = :turn_number 
                AND request = :request 
                LIMIT 1";
    }

    public static function selectIsWinner(): string
    {
        return "SELECT COUNT(*) FROM Shots 
                WHERE game_id = :game_id 
                AND player_id = :player_id 
                AND response >= :response";
    }

    public static function updateGamesWinner(): string
    {
        return "UPDATE Games 
                SET winner = :winner 
                WHERE id = :id";
    }

    public static function selectWinner(): string
    {
        return "SELECT winner FROM Games WHERE id = :game_id";
    }

    public static function selectStartCoordinate(): string
    {
        return "SELECT start_coord FROM Shots 
                WHERE player_id = :player_id 
                AND game_id = :game_id 
                AND target = :target 
                AND request = :request";
    }

    public static function selectShipLength(): string
    {
        return "SELECT ship_length FROM Shots 
                WHERE player_id = :player_id 
                AND game_id = :game_id 
                AND target = :target 
                AND request = :request";
    }

    public static function selectAfkCount(): string
    {
        return "SELECT COUNT(*) FROM Shots 
                WHERE game_id = :game_id 
                AND player_id = :player_id 
                AND target = :target 
                AND request >= :request";
    }

    public static function updateUserLastUpdate(): string
    {
        return "UPDATE Users 
                SET last_update = :last_update 
                WHERE id = :id";
    }

    public static function selectUserLastUpdate(): string
    {
        return "SELECT last_update FROM Users WHERE id = :id";
    }

    public static function deleteQueue(): string
    {
        return "DELETE FROM Queues WHERE user_id = :user_id AND status = :status";
    }

    public static function updateUser(): string
    {
        return "UPDATE Users 
                SET is_online = :is_online 
                WHERE id = :id";
    }

    public static function updateReconnectExit(): string
    {
        return "UPDATE Users 
                SET is_online = :is_online 
                WHERE id = :id";
    }

}