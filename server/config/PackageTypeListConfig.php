<?php

namespace server\config;

class PackageTypeListConfig
{
    public static function battlePackage(): array
    {
        return [
            "Set Shot request" => 'setShotRequest',
            "Set Shot response" => 'setShotResponse',
            "Listen Shot Response" => 'listenerShotResponse',
            "Listen Shot Request" => 'listenerShotRequest',
            "Force exit" => 'setGameWinnerExit',
            "Force back" => 'setGameWinnerBack',
            "Force exit winner" => 'setUserForceExit',
            "Force back winner" => 'setUserForceBack',
            "Force exit listener" => 'getGameWinnerExit',
            "Set user last update" => 'setUserLastUpdate',
            "Get user last update" => 'getUserLastUpdate',
            "Set timeout exit winner" => 'setTimeoutExitWinner',
            "Update user reconnect" => 'updateReconnectExit',
        ];
    }
}