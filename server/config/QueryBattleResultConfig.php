<?php

namespace server\config;

class QueryBattleResultConfig
{
    public static function deleteQueue(): string
    {
        return "DELETE FROM Queues WHERE user_id = :user_id AND status = :status";
    }

    public static function updateLogin(): string
    {
        return 'UPDATE Users SET is_online = :is_online WHERE id = :id';
    }
}