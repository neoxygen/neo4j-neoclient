<?php

namespace Neoxygen\NeoClient\HighAvailibility;

use Neoxygen\NeoClient\Connection\ConnectionManager;
use Neoxygen\NeoClient\Command\CommandManager;
use Neoxygen\NeoClient\Event\HttpExceptionEvent;
use Neoxygen\NeoClient\Event\PostRequestSendEvent;
use Neoxygen\NeoClient\Event\HttpClientPreSendRequestEvent;
use Neoxygen\NeoClient\NeoClientEvents;
use Neoxygen\NeoClient\HttpClient\GuzzleHttpClient;
use Neoxygen\NeoClient\Exception\HttpException;
use Neoxygen\NeoClient\Client;
use Neoxygen\NeoClient\Request\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Yaml;

class HAEnterpriseManager implements EventSubscriberInterface
{
    /**
     * @var \Neoxygen\NeoClient\Connection\ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var \Neoxygen\NeoClient\Command\CommandManager
     */
    protected $commandManager;

    /**
     * @var \Neoxygen\NeoClient\HttpClient\GuzzleHttpClient
     */
    protected $httpClient;

    /**
     * @var array
     */
    protected $slavesUsed = [];

    /**
     * @var array
     */
    protected $writeReplicationUsed = [];

    /**
     * @var
     */
    protected $masterUsed;

    /**
     * @var array
     */
    protected $fails = [];

    /**
     * @var null
     */
    protected $masterWriteFails = null;

    /**
     * @var
     */
    protected $newMasterDetected;

    /**
     * @var string
     */
    protected $queryModeHeaderName;

    /**
     * @var string
     */
    protected $queryModeWriteQueryHeaderValue;

    /**
     * @var string
     */
    protected $queryModeReadQueryHeaderValue;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            NeoClientEvents::NEO_HTTP_EXCEPTION => array(
                'onRequestException', 50,
            ),
            NeoClientEvents::NEO_PRE_REQUEST_SEND => array(
                'onPreSend', 50,
            ),
            NeoClientEvents::NEO_PRE_REQUEST_SEND => array(
                'onPreSendHAHeaders', 10,
            ),
            NeoClientEvents::NEO_POST_REQUEST_SEND => array(
                'onSuccessfulRequest', 30,
            ),
        );
    }

    /**
     * @param \Neoxygen\NeoClient\Connection\ConnectionManager $connectionManager
     * @param \Neoxygen\NeoClient\Command\CommandManager       $commandManager
     * @param \Neoxygen\NeoClient\HttpClient\GuzzleHttpClient  $httpClient
     */
    public function __construct(ConnectionManager $connectionManager, CommandManager $commandManager, GuzzleHttpClient $httpClient, $queryModeKey, $writeQueryKeyValue, $readQueryKeyValue)
    {
        $this->connectionManager = $connectionManager;
        $this->commandManager = $commandManager;
        $this->httpClient = $httpClient;
        $this->queryModeHeaderName = $queryModeKey;
        $this->queryModeWriteQueryHeaderValue = $writeQueryKeyValue;
        $this->queryModeReadQueryHeaderValue = $readQueryKeyValue;
    }

    /**
     * @param \Neoxygen\NeoClient\Event\HttpExceptionEvent $event
     */
    public function onRequestException(HttpExceptionEvent $event)
    {
        $request = $event->getRequest();
        $this->fails[$request->getConnection()] = !isset($this->fails[$request->getConnection()]) ? 1 : $this->fails[$request->getConnection()] + 1;
        if ($request->hasQueryMode()) {
            if ($request->getQueryMode() == 'READ') {
                $this->slavesUsed[] = $request->getConnection();
                if ($this->connectionManager->hasNextSlave($this->slavesUsed)) {
                    $next = $this->connectionManager->getNextSlave($this->slavesUsed);
                    Client::log('warning', sprintf('Connection "%s" unreacheable, using "%s"', $request->getConnection(), $next));
                    $request->setInfoFromConnection($this->connectionManager->getConnection($next));
                    $request->setQueryMode('READ');
                    $event->stopPropagation();
                } elseif (null === $this->masterUsed) {
                    $master = $this->connectionManager->getMasterConnection();
                    if (isset($master)) {
                        Client::log('warning', sprintf('Connection "%s" unreacheable, using "%s"', $request->getConnection(), $master->getAlias()));
                        $this->masterUsed = true;
                        $request->setInfoFromConnection($master);
                        $request->setQueryMode('READ');
                        $event->stopPropagation();
                    } else {
                        Client::log('warning', sprintf('Connection "%s" unreacheable, even after trying the master', $request->getConnection()));
                    }
                }
            } elseif ($request->getQueryMode() == 'WRITE') {
                Client::log('emergency', sprintf('The master connection "%s" is unreachable', $request->getConnection()));
                $newMaster = $this->detectReelectedMaster();
                if (null !== $newMaster) {
                    $conn = $this->connectionManager->getConnection($newMaster);
                    $this->masterWriteFails = $this->masterWriteFails + 1;
                    $this->newMasterDetected = $newMaster;
                    $request->setInfoFromConnection($conn);
                    $request->setQueryMode('WRITE');
                    $event->stopPropagation();
                }
            }
        }
    }

    /**
     * @param \Neoxygen\NeoClient\Event\HttpClientPreSendRequestEvent $event
     */
    public function onPreSend(HttpClientPreSendRequestEvent $event)
    {
        $request = $event->getRequest();
        $conn = $request->getConnection();
        if (isset($this->fails[$conn]) && $this->fails[$conn] >= 5) {
            if ($request->hasQueryMode()) {
                if ($request->getQueryMode() === 'READ') {
                    if ($this->connectionManager->hasNextSlave([$conn])) {
                        $next = $this->connectionManager->getNextSlave([$conn]);
                        $this->setHaPrimarySlave($next);
                        $request->setInfoFromConnection($this->connectionManager->getConnection($next));
                    }
                }
            }
        }

        if (null !== $this->masterWriteFails && $request->getQueryMode() == 'WRITE' && $this->masterWriteFails >= 5) {
            if (null !== $this->newMasterDetected) {
                $this->setHaNewMaster($this->newMasterDetected);
                $conn = $this->connectionManager->getConnection($this->newMasterDetected);
                Client::log('debug', sprintf('Automatic Write connection change after 5 write failures on Master. Changing to the "%s" connection', $this->newMasterDetected));
                $request->setInfoFromConnection($conn);
            }
        }
    }

    /**
     * Add specific headers to the Request object for helping HA proxies to determine if it is a read or write query.
     *
     * @param \Neoxygen\NeoClient\Event\HttpClientPreSendRequestEvent $event
     */
    public function onPreSendHAHeaders(HttpClientPreSendRequestEvent $event)
    {
        if ($event->getRequest()->getQueryMode() == 'WRITE') {
            $event->getRequest()->setHeader($this->queryModeHeaderName, $this->queryModeWriteQueryHeaderValue);
        } elseif ($event->getRequest()->getQueryMode() == 'READ') {
            $event->getRequest()->setHeader($this->queryModeHeaderName, $this->queryModeReadQueryHeaderValue);
        }
    }

    /**
     * @param \Neoxygen\NeoClient\Event\PostRequestSendEvent $event
     */
    public function onSuccessfulRequest(PostRequestSendEvent $event)
    {
        $request = $event->getRequest();
        $this->fails[$request->getConnection()] = null;
        $this->slavesUsed = [];
        $this->masterUsed = null;
    }

    /**
     *
     */
    private function detectReelectedMaster()
    {
        $slaves = $this->connectionManager->getSlaves();
        foreach ($slaves as $slave) {
            if ($this->isMaster($slave)) {
                Client::log('debug', sprintf('Master Reelection detected, new Master is "%s".', $slave));

                return $slave;
            }
        }

        return;
    }

    /**
     * @param $connAlias
     *
     * @return bool
     */
    private function isMaster($connAlias)
    {
        $command = $this->commandManager->getCommand('neo.core_get_ha_master');
        $command->setConnection($connAlias);
        try {
            $response = $command->execute();
            if ($response instanceof Response && true === $response->getBody()) {
                return true;
            }
        } catch (HttpException $e) {
            return false;
        }

        return false;
    }

    /**
     * @param array $config
     */
    private function setHAConfigAfterFailure(array $config)
    {
        $dump = Yaml::dump($config, 4, 2);
        $file = $this->getHAFailureFile();
        file_put_contents($file, $dump);
    }

    /**
     * @return array
     */
    private function getHAConfigAfterFailure()
    {
        if (!file_exists($this->getHAFailureFile())) {
            return array();
        }
        $content = file_get_contents($this->getHAFailureFile());
        $config = Yaml::parse($content);

        return $config;
    }

    /**
     * Retrieve the new HAconfig after a failure.
     */
    private function getHAFailureFile()
    {
        $dir = sys_get_temp_dir();
        $file = $dir.DIRECTORY_SEPARATOR.'neoclient_ha_config_after_failure';

        return $file;
    }

    /**
     * @param $slaveAlias
     */
    private function setHaPrimarySlave($slaveAlias)
    {
        $config = $this->getHAConfigAfterFailure();
        $config['primary_slave'] = $slaveAlias;
        $this->setHAConfigAfterFailure($config);
    }

    /**
     * @param $masterAlias
     */
    private function setHaNewMaster($masterAlias)
    {
        $config = $this->getHAConfigAfterFailure();
        $config['new_master'] = $masterAlias;
        $this->setHAConfigAfterFailure($config);
    }
}
