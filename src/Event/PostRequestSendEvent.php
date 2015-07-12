<?php

namespace Neoxygen\NeoClient\Event;

use Symfony\Component\EventDispatcher\Event;
use Neoxygen\NeoClient\Request\RequestInterface;
use GuzzleHttp\Psr7\Response;

class PostRequestSendEvent extends Event
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var
     */
    protected $response;

    /**
     * @param RequestInterface $request
     * @param Response         $response
     */
    public function __construct(RequestInterface $request, Response $response)
    {
        $this->request = $request;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}
