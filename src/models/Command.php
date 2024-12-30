<?php

namespace light\tg\bot\models;

use light\tg\bot\Bot;
use TelegramBot\Api\Types\Message;


abstract class Command
{
    protected $_bot;

    abstract public static function getTitle(): string;

    abstract public function run(): void;


    public function __construct(Bot $bot)
    {
        $this->_bot = $bot;
    }


    public function getBot(): Bot
    {
        return $this->_bot;
    }


    public function getUserId(): int
    {
        return $this->getBot()->getIncomeMessage()->getSenderId();
    }
}