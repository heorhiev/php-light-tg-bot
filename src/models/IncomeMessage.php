<?php

namespace light\tg\bot\models;


use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\User;
use TelegramBot\Api\Types\UserShared;

class IncomeMessage
{
    private $id;
    private $callbackId;
    private $chat;
    private $from;
    private $command;
    private $params;
    private $userName;
    private $text;
    private $isCallbackQuery;
    private $threadId;
    private $files;
    private $userShared;

    private BotApi $api;


    public function __construct($update, BotApi $api)
    {
        $this->api = $api;

        if ($update->getMessage()) {
            $this->mapMessage($update->getMessage());
        } elseif ($update->getCallbackQuery()) {
            $this->mapCallbackQuery($update->getCallbackQuery());
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCallbackId()
    {
        return $this->callbackId;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function isCallbackQuery()
    {
        return $this->isCallbackQuery;
    }

    public function isEdited(): bool
    {
        return $this->isCallbackQuery;
    }

    public function getChat()
    {
        return $this->chat;
    }


    public function getFrom(): User
    {
        return $this->from;
    }


    public function getSenderFullName(): string
    {
        return trim($this->getFrom()->getFirstName() . ' ' . $this->getFrom()->getLastName());
    }

    public function getSenderId(): int
    {
        return $this->getChat()->getId();
    }


    public function getText()
    {
        return $this->text;
    }


    public function getThreadId()
    {
        return $this->threadId;
    }


    public function getFiles()
    {
        return $this->files;
    }


    /**
     * @return UserShared|null
     */
    public function getUserShared()
    {
        return $this->userShared;
    }


    public function delete(): bool
    {
        return $this->api->deleteMessage($this->chat->getId(), $this->getId());
    }


    private function mapMessage(\TelegramBot\Api\Types\Message $message): void
    {
        $this->id = $message->getMessageId();
        $this->chat = $message->getChat();
        $this->from = $message->getFrom();
        $this->text = $message->getText() ?: $message->getCaption();
        $this->threadId = $message->getMessageThreadId();

        if (method_exists($message, 'getUserShared')) {
            $this->userShared = $message->getUserShared();
        }

        if ($message->getDocument()) {
            $this->files[] = $message->getDocument();
        }

        if ($message->getAudio()) {
            $this->files[] = $message->getAudio();
        }

        $this->parseCommand($this->text);
    }

    private function mapCallbackQuery($callbackQuery)
    {
        $this->mapMessage($callbackQuery->getMessage());
        $this->parseCommand($callbackQuery->getData());

        $this->isCallbackQuery = true;
        $this->callbackId = $callbackQuery->getId();
    }


    private function parseCommand($text): void
    {
        if ($text[0] == '/') {
            $parts = explode(' ', $text);

            if (count($parts) == 1) {
                $parts = explode(PHP_EOL, $text);
            }

            $this->command = isset($parts[0]) ? substr($parts[0], 1) : '';
            unset($parts[0]);
            $this->params = join(' ', $parts);
        }
    }
}