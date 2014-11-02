<?php

namespace Neoxygen\NeoClient\Event;

use Symfony\Component\EventDispatcher\Event;
use Neoxygen\NeoClient\Request\RequestInterface;
use GuzzleHttp\Exception\RequestException;

class HttpExceptionEvent extends Event
{
    protected $request;

    protected $exception;

    public function __construct(RequestInterface $request, RequestException $exception)
    {
        $this->request = $request;
        $this->exception = $exception;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function getException()
    {
        return $this->exception;
    }
}
