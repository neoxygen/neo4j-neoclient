<?php

namespace Neoxygen\NeoClient\Request;

use Neoxygen\NeoClient\Formatter\Result;
use Psr\Http\Message\ResponseInterface;

class Response
{
    protected $body;

    protected $rows;

    protected $result;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $raw;

    public function __construct(ResponseInterface $responseInterface)
    {
        $this->raw = $responseInterface;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function hasBody()
    {
        return null !== $this->body;
    }

    /**
     * @return mixed
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param mixed $rows
     */
    public function setRows($rows = null)
    {
        $this->rows = $rows;
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult(Result $result = null)
    {
        $this->result = $result;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getRaw()
    {
        return $this->raw;
    }
}
