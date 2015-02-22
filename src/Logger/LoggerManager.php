<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Logger;

use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggerManager implements LoggerInterface
{
    protected $loggers;

    public function setLogger($name, LoggerInterface $logger)
    {
        if (!isset($this->loggers[$name])) {
            $this->loggers[$name] = $logger;
        }

        return $this;
    }

    public function getLogger($name = 'defaultLogger')
    {
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
            switch ($config['type']) {
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

    public function emergency($message, array $context = array())
    {
        return $this->log('emergency', $message, $context);
    }

    public function alert($message, array $context = array())
    {
        return $this->log('alert', $message, $context);
    }

    public function critical($message, array $context = array())
    {
        return $this->log('critical', $message, $context);
    }

    public function error($message, array $context = array())
    {
        return $this->log('error', $message, $context);
    }

    public function warning($message, array $context = array())
    {
        return $this->log('warning', $message, $context);
    }

    public function notice($message, array $context = array())
    {
        return $this->log('notice', $message, $context);
    }

    public function info($message, array $context = array())
    {
        return $this->log('info', $message, $context);
    }

    public function debug($message, array $context = array())
    {
        return $this->log('debug', $message, $context);
    }
}
