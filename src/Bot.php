<?php

namespace light\tg\bot;

use light\tg\bot\config\MenuDto;
use light\tg\bot\config\TelegramDto;
use light\app\services\SettingsService;
use light\tg\bot\models\{Command, IncomeMessage, Message};
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;


abstract class Bot
{
    private $_options;
    private $_botApi;
    private $_dataFromRequest;
    private $_incomeMessage;


    /**
     * @return Command[]
     */
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

        if (!$class) {
            $class = $this->getDefaultHandler();
        }

        if ($class) {
            $this->storeCommand($class);
        } else {
            $class = $this->getStoredCommand();
        }

        (new $class($this))->run();
    }


    public function getCommandHandler($command): ?Command
    {
        $commands = static::getCommands();
        return $commands[$command] ?? null;
    }


    public function getTextHandler($text): ?Command
    {
        foreach ($this->getMenu() as $menu) {
            if ($text == $menu->label) {
                return $this->getCommandHandler($menu->code);
            }
        }

        foreach (static::getCommands() as $command) {
            if ($text == $command::getTitle()) {
                return $command;
            }
        }

        return null;
    }


    public function getDefaultHandler()
    {
        return null;
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


    /**
     * @return MenuDto[]
     */
    public function getMenu(): array
    {
        $items = $this->getOptions()->menu;

        if ($items) {
            foreach ($items as $item) {
                $menu[] = new MenuDto($item);
            }
        }

        return $menu ?? [];
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


    public function storeCommand($command, string $data = ''): bool
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
                $message->getKeyboardMarkup()
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
