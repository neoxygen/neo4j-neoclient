<?php

namespace Neoxygen\NeoClient\Exception;

class Neo4jException extends \Exception
{
    const NEO4J_INDEX_ALREADY_EXIST = 8000;

    public static function fromCode($code)
    {
        $c = (string) $code;
        switch ($c) {
            case 'Neo.ClientError.Schema.IndexAlreadyExists':
                return self::NEO4J_INDEX_ALREADY_EXIST;
        }
    }
}
