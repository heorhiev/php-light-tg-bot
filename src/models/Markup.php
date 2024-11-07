<?php

namespace light\tg\bot\models;


class Markup
{
    private $command;
    private $query;

    public function __construct($command, $query = [])
    {
        $this->command = $command;
        $this->query = $query;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getQuery()
    {
        return $this->query;
    }
}