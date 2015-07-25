<?php

namespace Neoxygen\NeoClient\Event;

use Symfony\Component\EventDispatcher\Event;
use Neoxygen\NeoClient\Request\RequestInterface;

class HttpClientPreSendRequestEvent extends Event
{
    /**
     * @var \Neoxygen\NeoClient\Request\Request
     */
    protected $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Neoxygen\NeoClient\Request\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }
}
