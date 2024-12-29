<?php

namespace light\tg\bot\override\TelegramBot\Api\Types;


class Update extends \TelegramBot\Api\Types\Update
{
    public static function fromResponse($data)
    {
        self::$map['message'] = Message::class;

        return parent::fromResponse($data);
    }
}