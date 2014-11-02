<?php

namespace Neoxygen\NeoClient\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Neoxygen\NeoClient\Event\HttpClientPreSendRequestEvent,
    Neoxygen\NeoClient\Event\PostRequestSendEvent,
    Neoxygen\NeoClient\Event\HttpExceptionEvent,
    Neoxygen\NeoClient\NeoClientEvents,
    Neoxygen\NeoClient\Exception\HttpException;

class HttpRequestEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            NeoClientEvents::NEO_PRE_REQUEST_SEND => array(
                'onPreHttpRequestSend'
            ),
            NeoClientEvents::NEO_POST_REQUEST_SEND => array(
                'onPostRequestSend'
            ),
            NeoClientEvents::NEO_HTTP_EXCEPTION => array(
                'onHttpException'
            )
        );
    }

    public function onPreHttpRequestSend(HttpClientPreSendRequestEvent $event)
    {
        $request = $event->getRequest();
    }

    public function onPostRequestSend(PostRequestSendEvent $event)
    {
        $request = $event->getRequest();
    }

    public function onHttpException(HttpExceptionEvent $event)
    {
        $request = $event->getRequest();
        $exception = $event->getException();

        $message = $exception->getMessage();
        throw new HttpException(sprintf('%s', $message));
    }
}
