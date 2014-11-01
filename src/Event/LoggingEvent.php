<?php

namespace Neoxygen\NeoClient\Event;

use Symfony\Component\EventDispatcher\Event;

class LoggingEvent extends Event
{
    protected $level;

    protected $message;

    protected $context;

    public function __construct($level = 'DEBUG', $message, $context)
    {
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getContext()
    {
        return $this->context;
    }
}
