<?php

namespace light\tg\bot\override\TelegramBot\Api\Types;

use TelegramBot\Api\Types\UsersShared;


class Message extends \TelegramBot\Api\Types\Message
{
    private $userShared;

    public function setUsersShared(UsersShared $data)
    {
        $this->userShared = $data;
    }


    public function getUsersShared()
    {
        return $this->userShared;
    }
}