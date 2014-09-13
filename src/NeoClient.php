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

namespace Neoxygen\NeoClient;

class NeoClient
{
    private static $serviceContainer;

    public static function getServiceContainer()
    {
        if (null === self::$serviceContainer) {
            self::$serviceContainer = new ServiceContainer();
        }

        return self::$serviceContainer;
    }

    public static function invoke($command, $connectionAlias = null)
    {
        return self::$serviceContainer->invoke($command, $connectionAlias);
    }

    public static function getRoot($connectionAlias = null)
    {
        return self::$serviceContainer->getRoot($connectionAlias);
    }

    public static function ping($connectionAlias = null)
    {
        return self::$serviceContainer->ping($connectionAlias);
    }

    public static function getDataEndpoints($connectionAlias = null)
    {
        return self::$serviceContainer->getDataEnpoints();
    }

    public static function getLabels($connectionAlias = null)
    {
        return self::$serviceContainer->getLabels($connectionAlias);
    }

    public static function getNeo4jVersion($connectionAlias = null)
    {
        return self::$serviceContainer->getVersion($connectionAlias);
    }

    public static function sendQuery($query, array $parameters = array(), $connectionAlias = null)
    {
        return self::$serviceContainer->sendCypherQuery($query, $parameters);
    }

    public static function openTransaction($connectionAlias = null)
    {
        return self::$serviceContainer->openTransaction($connectionAlias);
    }

    public static function rollbackTransaction($id, $connectionAlias = null)
    {
        return self::$serviceContainer->rollbackTransaction($id, $connectionAlias);
    }

    public static function push($transactionId, $query, array $parameters = array(), $connectionAlias = null)
    {
        return self::$serviceContainer->pushToTransaction($transactionId, $query, $parameters, $connectionAlias);
    }

    public static function send($method, $url, $body = null, array $headers = array())
    {
        return self::$serviceContainer->getHttpClient()->send($method, $url, $body, $headers);
    }

    public static function log($level = 'debug', $message, array $context = array())
    {
        return self::$serviceContainer->log($level, $message, $context);
    }

}
