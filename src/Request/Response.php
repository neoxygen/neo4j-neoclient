<?php

namespace Neoxygen\NeoClient\Request;

use Neoxygen\NeoClient\Formatter\Result;

class Response
{
    protected $body;

    protected $rows;

    protected $result;

    public function __construct()
    {
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
}
