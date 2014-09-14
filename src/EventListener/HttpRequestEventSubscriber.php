<?php

namespace Neoxygen\NeoClient\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Neoxygen\NeoClient\Event\HttpClientPreSendRequestEvent,
    Neoxygen\NeoClient\NeoClientEvents;

class HttpRequestEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            NeoClientEvents::NEO_HTTP_PRE_REQUEST_SEND => array(
                'onPreHttpRequestSend'
            )
        );
    }

    public function onPreHttpRequestSend(HttpClientPreSendRequestEvent $event)
    {
        //var_dump($event);
    }
}