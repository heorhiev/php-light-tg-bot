<?php

namespace light\tg\bot;

use light\tg\bot\config\TelegramDto;
use light\app\services\SettingsService;
use light\tg\bot\models\{IncomeMessage, Message};
use TelegramBot\Api\{BotApi, Types\Update};


abstract class Bot
{
    private $_options;
    private $_botApi;
    private $_dataFromRequest;
    private $_incomeMessage;


    abstract public static function getCommands(): array;


    public function __construct(string $configFile)
    {
        /** @var TelegramDto $options */
        $this->_options = SettingsService::load($configFile, TelegramDto::class);
        $this->_botApi = new BotApi($this->_options->token);
    }


    public function run($data = null): void
    {
        if (!$data) {
            $data = BotApi::jsonValidate(file_get_contents('php://input'), true);
        }

        $this->_dataFromRequest = Update::fromResponse($data);

        $class = $this->getCommandHandler($this->getIncomeMessage()->getCommand());

        if (!$class) {
            $class = $this->getTextHandler($this->getIncomeMessage()->getText());
        }

        if ($class) {
            $this->storeCommand($class);
        } else {
            $class = $this->getStoredCommand();
        }

        (new $class($this))->run();
    }


    public function getCommandHandler($command)
    {
        $commands = static::getCommands();
        return $commands[$command] ?? null;
    }


    public function getTextHandler($text)
    {
        foreach ($this->getMenu() as $command => $menuText) {
            if ($text == $menuText) {
                return $this->getCommandHandler($command);
            }
        }
    }


    public function getOptions(): TelegramDto
    {
        return $this->_options;
    }


    public function getBotApi(): BotApi
    {
        return $this->_botApi;
    }


    public function getDataFromRequest(): Update
    {
        return $this->_dataFromRequest;
    }


    public function getMenu(): ?array
    {
        return $this->getOptions()->menu;
    }


    public function getIncomeMessage(): IncomeMessage
    {
        if (!$this->_incomeMessage) {
            $this->_incomeMessage = new IncomeMessage($this->_dataFromRequest);
        }

        return $this->_incomeMessage;
    }


    public function getUserId(): int
    {
        return $this->getIncomeMessage()->getSenderId();
    }


    public function getStoredCommand(): ?string
    {
        return null;
    }


    public function storeCommand($command): bool
    {
        return false;
    }


    public function getNewMessage(): Message
    {
        return (new Message($this->getOptions()))->setRecipientId($this->getUserId());
    }


    public function answerCallbackQuery($text = '', $popup = false)
    {
        $this->getBotApi()->answerCallbackQuery(
            $this->getIncomeMessage()->getCallbackId(),
            $text,
            $popup
        );
    }


    public function sendMessage(Message $message, $acceptEdit = false, $closeCallback = true)
    {
        if ($closeCallback && $this->getIncomeMessage()->isCallbackQuery()) {
            $this->answerCallbackQuery();
        }

        if ($acceptEdit && $this->getIncomeMessage()->isEdited()) {
            return $this->getBotApi()->editMessageText(
                $message->getRecipientId(),
                $this->getIncomeMessage()->getId(),
                $message->getRenderedContent(),
                'HTML',
                true,
                $message->getKeyboard()
            );
        } else {
            $this->getBotApi()->sendMessage(
                $message->getRecipientId(),
                $message->getRenderedContent(),
                'HTML',
                true,
                null,
                $message->getKeyboardMarkup(),
                false,
                $message->getMessageThreadId()
            );
        }
    }
}
