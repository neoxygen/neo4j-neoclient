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
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;

class HttpRequestEventSubscriber implements EventSubscriberInterface
{
    protected $logger;

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

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onPreHttpRequestSend(HttpClientPreSendRequestEvent $event)
    {
        $conn = $event->getRequest()->getConnection();
        $request = $event->getRequest();
        $mode = $request->hasQueryMode() ? $request->getQueryMode() : 'ASSUMED WRITE';
        $this->logger->log('debug', sprintf('Sending "%s" request to the "%s" connection', $mode,  $conn));
        $this->sendGA();
    }

    public function onPostRequestSend(PostRequestSendEvent $event)
    {
    }

    public function onHttpException(HttpExceptionEvent $event)
    {
        $request = $event->getRequest();
        $exception = $event->getException();
        $message = $exception->getMessage();
        Client::log('emergency', sprintf('Error on connection "%s" - %s', $request->getConnection(), $message));
        throw new HttpException(sprintf('Error on Connection "%s" with message "%s"', $request->getConnection(), $message));
    }

    private function sendGA()
    {
        $hc = new HttpClient();
        $i = gethostbyname(gethostname());
        $r = $hc->createRequest('POST', 'http://www.google-analytics.com/collect');
        $r->setQuery([
            'v' => 1,
            'tid' => 'UA-58561434-1',
            'cid' => sha1($i),
            't' => 'event',
            'ec' => 'Run' . Client::getNeoClientVersion(),
            'ea' => 'NeoClient',
            'el' => Client::getNeoClientVersion()
        ]);
        try {
            $hc->send($r);
        } catch (RequestException $e) {

        }
    }
}
