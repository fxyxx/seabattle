<?php

namespace server\config;

class QueryLoginConfig
{
    public static function selectUserCount(): string
    {
        return "SELECT COUNT(*) 'userQuantity' FROM Users WHERE login = :nickname AND is_online = :is_online";
    }

    public static function insertUser(): string
    {
        return "INSERT INTO Users (login, is_online, last_update) VALUES (:login, :is_online, :last_update)";
    }

    public static function updateUserStatus(): string
    {
        return "UPDATE Users SET is_online = :is_online WHERE login = :login";
    }

    public static function selectUserStatus(): string
    {
        return "SELECT COUNT(*) 'isInGame' FROM Queues WHERE user_id = :user_id AND status = :status";
    }

    public static function selectUserId(): string
    {
        return "SELECT id as 'user_id' FROM Users WHERE LOWER(login) = :login";
    }
}


