<?php
namespace Controllers;

use Exception;
use Models\BattleResultModel;

class BattleResultController
{
    private BattleResultModel $battleResultModel;

    public function __construct(BattleResultModel $battleResultModel)
    {
        $this->battleResultModel = $battleResultModel;
    }

    /**
     * Sets the user's play again status and deletes them from the game queue.
     *
     * @param array $postData Data received from the request.
     *
     * @return array Response after setting the play again status and deleting from the queue.
     * @throws Exception
     */
    public function setUserPlayAgain(array $postData): array
    {
        $response = [];
        $myId = $postData['myId'];

        $this->battleResultModel->deleteQueue($myId, 2);
        $response['isUserPlayAgain'] = $postData;

        return $response;
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
        $myId = $postData['myId'];

        $this->battleResultModel->updateLogin($myId, 0);
        $response['isUserLeave'] = $postData;

        return $response;
    }

}
