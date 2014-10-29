<?php

namespace Neoxygen\NeoClient\Formatter;

class ResponseFormatterManager
{
    private $responseFormatter;

    public function __construct($responseFormatter)
    {
        $this->responseFormatter = new $responseFormatter();
    }

    public function getResponseFormatter()
    {
        return $this->responseFormatter;
    }
}
