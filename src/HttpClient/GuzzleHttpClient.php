<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Neoxygen\NeoClient\HttpClient;

use GuzzleHttp\Client,
    GuzzleHttp\Exception\RequestException,
    GuzzleHttp\Message\Response as HttpResponse;
use Neoxygen\NeoClient\Request\Request,
    Neoxygen\NeoClient\Request\Response,
    Neoxygen\NeoClient\NeoClientEvents,
    Neoxygen\NeoClient\Event\HttpClientPreSendRequestEvent,
    Neoxygen\NeoClient\Event\PostRequestSendEvent,
    Neoxygen\NeoClient\Event\HttpExceptionEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    private $client;

    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->client = new Client();
        $this->eventDispatcher = $eventDispatcher;
    }

    public function sendRequest(Request $request)
    {
        $this->dispatchPreSend($request);
        $defaults = [];
        if ($request->hasBody()){
            $defaults['body'] = $request->getBody();
        }
        if ($request->hasQueryStrings()) {
            $defaults['query'] = $request->getQueryStrings();
        }
        if ($request->isSecured()) {
            $defaults['auth'] = [$request->getUser(), $request->getPassword()];
        }
        $defaults['timeout'] = null !== $request->getTimeout() ? $request->getTimeout() : 1;

        $httpRequest = $this->client->createRequest($request->getMethod(), $request->getUrl(), $defaults);
        $httpRequest->setHeader('Content-Type', 'application/json');
        $httpRequest->setHeader('Accept', 'application/json');


        try {
            $response = $this->client->send($httpRequest);
            $this->dispatchPostRequestSend($request, $response);
            return $this->getResponse($response);
        } catch (RequestException $e) {
            $this->dispatchHttpException($request, $e);
        }


    }

    private function getResponse(HttpResponse $httpResponse)
    {
        $response = new Response();

        if ($httpResponse->getBody()) {
            $resp = (string) $httpResponse->getBody();
            $decoded = \GuzzleHttp\json_decode($resp, true);
            $response->setBody($decoded);
            }

        return $response;
    }

    private function dispatchPreSend(Request $request)
    {
        $event = new HttpClientPreSendRequestEvent($request);
        $this->eventDispatcher->dispatch(NeoClientEvents::NEO_PRE_REQUEST_SEND, $event);
    }

    private function dispatchPostRequestSend(Request $request, HttpResponse $response)
    {
        $event = new PostRequestSendEvent($request, $response);
        $this->eventDispatcher->dispatch(NeoClientEvents::NEO_POST_REQUEST_SEND, $event);
    }

    private function dispatchHttpException(Request $request, RequestException $exception)
    {
        $event = new HttpExceptionEvent($request, $exception);
        $this->eventDispatcher->dispatch(NeoClientEvents::NEO_HTTP_EXCEPTION, $event);
    }
}
