<?php

namespace light\tg\bot\models;


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


    public function __construct($update)
    {
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


    public function getFrom()
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


    private function mapMessage(\TelegramBot\Api\Types\Message $message)
    {
        $this->id = $message->getMessageId();
        $this->chat = $message->getChat();
        $this->from = $message->getFrom();
        $this->text = $message->getText() ? $message->getText() : $message->getCaption();
        $this->threadId = $message->getMessageThreadId();

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

        file_put_contents('/var/www/keycrm/base/api_html/vipcall/runtime/logs/test.txt', print_r($callbackQuery, 1));
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