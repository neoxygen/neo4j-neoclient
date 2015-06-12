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

    protected $hc;

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
        $this->hc = new HttpClient();
    }

    public function onPreHttpRequestSend(HttpClientPreSendRequestEvent $event)
    {
        $conn = $event->getRequest()->getConnection();
        $request = $event->getRequest();
        $mode = $request->hasQueryMode() ? $request->getQueryMode() : 'ASSUMED WRITE';
        $this->logger->log('debug', sprintf('Sending "%s" request to the "%s" connection', $mode,  $conn));
        if (false !== false) {
            $this->sendGA();
        }

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
        $td = sys_get_temp_dir();
        if (!is_writable($td)) { return; }
        $f = $td . DIRECTORY_SEPARATOR . 'neoping.txt';
        $c = $td . DIRECTORY_SEPARATOR . 'neoi.txt';
        $t = time();
        if (file_exists($f) && file_exists($c)) {
            $last = (int) file_get_contents($f);
            $new = false;
            $ci = file_get_contents($c);
        } else {
            $last = 0;
            $ci = sha1($t);
            $new = true;
        }
        if (($t - $last) < 120) { return; }
        $r = $this->hc->createRequest('GET', 'http://stats.neoxygen.io/collect', array('timeout' => 1));
        $r->setQuery([
            'v' => Client::getNeoClientVersion(),
            'cid' => $ci,
        ]);
        try {
            $this->hc->send($r);
            $this->lc = $t;
            file_put_contents($f, $t);
            if ($new) {
                file_put_contents($c, $ci);
            }
        } catch (RequestException $e) {
        }
    }
}
