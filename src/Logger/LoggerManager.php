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

namespace Neoxygen\NeoClient\Logger;

use Psr\Log\NullLogger,
    Psr\Log\LoggerInterface;
use Monolog\Logger,
    Monolog\Handler\StreamHandler;

class LoggerManager
{
    protected $loggers;

    public function setLogger($name, LoggerInterface $logger)
    {
        if (!isset($this->loggers[$name])) {
            $this->loggers[$name] = $logger;
        }

        return $this;
    }

    public function getLogger($name = 'defaultLogger') {
        if (!isset($this->loggers[$name])) {
            if ('defaultLogger' === $name) {
                $this->loggers[$name] = new NullLogger();
            }
        }

        return $this->loggers[$name];
    }

    public function createLogger($name, $config)
    {
        if (!isset($this->loggers[$name])) {

            $logger = new Logger($name);
            switch($config['type']) {
                case 'stream':
                    $handler = new StreamHandler(
                        $config['path'],
                        isset($config['level']) ? $config['level'] : null
                        );
                    $logger->pushHandler($handler);
                    $this->loggers[$name] = $logger;
                    break;
            }
        }

        return $this->loggers[$name];
    }

    public function log($level = 'debug', $message, array $context = array())
    {
        if (empty($this->loggers)) {
            $this->loggers['defaultLogger'] = new NullLogger();
        }
        foreach ($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }

        return true;
    }
}