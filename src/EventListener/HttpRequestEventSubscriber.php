<?php

namespace Neoxygen\NeoClient\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Neoxygen\NeoClient\Event\HttpClientPreSendRequestEvent;
use Neoxygen\NeoClient\Event\PostRequestSendEvent;
use Neoxygen\NeoClient\Event\HttpExceptionEvent;
use Neoxygen\NeoClient\NeoClientEvents;
use Neoxygen\NeoClient\Exception\HttpException;
use Neoxygen\NeoClient\Client;
use Psr\Log\LoggerInterface;

class HttpRequestEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            NeoClientEvents::NEO_PRE_REQUEST_SEND => array(
                'onPreHttpRequestSend', 10,
            ),
            NeoClientEvents::NEO_POST_REQUEST_SEND => array(
                'onPostRequestSend',
            ),
            NeoClientEvents::NEO_HTTP_EXCEPTION => array(
                'onHttpException', 10,
            ),
        );
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param \Neoxygen\NeoClient\Event\HttpClientPreSendRequestEvent $event
     */
    public function onPreHttpRequestSend(HttpClientPreSendRequestEvent $event)
    {
        $conn = $event->getRequest()->getConnection();
        $request = $event->getRequest();
        $mode = $request->hasQueryMode() ? $request->getQueryMode() : 'ASSUMED WRITE';
        $this->logger->log('debug', sprintf('Sending "%s" request to the "%s" connection', $mode,  $conn));
    }

    /**
     * @param \Neoxygen\NeoClient\Event\PostRequestSendEvent $event
     */
    public function onPostRequestSend(PostRequestSendEvent $event)
    {
    }

    /**
     * @param \Neoxygen\NeoClient\Event\HttpExceptionEvent $event
     */
    public function onHttpException(HttpExceptionEvent $event)
    {
        $request = $event->getRequest();
        $exception = $event->getException();
        $message = $exception->getMessage();
        Client::log('emergency', sprintf('Error on connection "%s" - %s', $request->getConnection(), $message));
        throw new HttpException(sprintf('Error on Connection "%s" with message "%s"', $request->getConnection(), $message));
    }
}
