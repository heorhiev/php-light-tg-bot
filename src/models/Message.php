<?php

namespace light\tg\bot\models;

use light\tg\bot\config\TelegramDto;
use light\app\services\AliasService;
use light\app\services\RenderService;


class Message
{
    private $_options;
    private $_keyboardMarkup;
    private $_lang;
    private $_recipientId;
    private $_messageThreadId;
    private $_messageView;
    private $_messageText;
    private $_attributes = [];


    public function __construct(TelegramDto $options)
    {
        $this->_options = $options;
    }


    public function getRecipientId(): ?int
    {
        return $this->_recipientId;
    }


    public function setRecipientId($recipientId): self
    {
        $this->_recipientId = $recipientId;
        return $this;
    }


    public function getMessageThreadId(): ?int
    {
        return $this->_messageThreadId;
    }


    public function setMessageThreadId(int $messageThreadId): Message
    {
        $this->_messageThreadId = $messageThreadId;
        return $this;
    }


    public function setKeyboardMarkup($keyboardMarkup): Message
    {
        $this->_keyboardMarkup = $keyboardMarkup;
        return $this;
    }


    public function getKeyboardMarkup()
    {
        return $this->_keyboardMarkup;
    }


    public function setLang(string $lang): Message
    {
        $this->_lang = $lang;
        return $this;
    }


    public function setMessageView(string $view): Message
    {
        $this->_messageView = $view;
        return $this;
    }


    public function setMessageText(string $text): Message
    {
        $this->_messageText = $text;
        return $this;
    }


    public function setAttributes(array $attributes = []): Message
    {
        $this->_attributes = $attributes;
        return $this;
    }


    public function getRenderedContent(): ?string
    {
        $content = $this->_messageText;

        if (!$content) {
            $path = AliasService::getPath($this->_options->viewDirectory . '/' . $this->_messageView);

            if ($this->_lang) {
                $langPath = $path . '.' . $this->_lang;

                if (RenderService::exists($langPath)) {
                    $path = $langPath;
                }
            }

            $content = RenderService::get($path, $this->_attributes);
        }

        return $content;
    }
}