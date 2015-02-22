<?php

namespace Neoxygen\NeoClient\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Neoxygen\NeoClient\Event\LoggingEvent;
use Neoxygen\NeoClient\NeoClientEvents;
use Psr\Log\LoggerInterface;

class LoggingEventSubscriber implements EventSubscriberInterface
{
    private $logger;

    public static function getSubscribedEvents()
    {
        return array(
            NeoClientEvents::NEO_LOG_MESSAGE => array(
                'writeLogMessage',
            ),
        );
    }

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function writeLogMessage(LoggingEvent $event)
    {
        $level = strtoupper($event->getLevel());
        $message = (string) $event->getMessage();
        $context = $event->getContext();
        if (!is_array($context)) {
            $context = array();
        }
        $this->logger->log($level, $message, $context);
    }
}
