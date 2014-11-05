<?php

namespace Neoxygen\NeoClient\HighAvailibility;

use Neoxygen\NeoClient\Connection\ConnectionManager,
    Neoxygen\NeoClient\Command\CommandManager,
    Neoxygen\NeoClient\Event\HttpExceptionEvent,
    Neoxygen\NeoClient\Event\PostRequestSendEvent,
    Neoxygen\NeoClient\Event\HttpClientPreSendRequestEvent,
    Neoxygen\NeoClient\NeoClientEvents,
    Neoxygen\NeoClient\HttpClient\GuzzleHttpClient,
    Neoxygen\NeoClient\Exception\HttpException,
    Neoxygen\NeoClient\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HAEnterpriseManager implements EventSubscriberInterface
{
    protected $connectionManager;

    protected $commandManager;

    protected $logger;

    protected $httpClient;

    protected $slavesUsed = [];

    protected $writeReplicationUsed = [];

    protected $masterUsed;

    protected $fails = [];

    protected $masterWriteFails = null;

    protected $newMasterDetected;

    public static function getSubscribedEvents()
    {
        return array(
            NeoClientEvents::NEO_HTTP_EXCEPTION => array(
                'onRequestException', 50
            ),
            NeoClientEvents::NEO_PRE_REQUEST_SEND => array(
                'onPreSend', 50
            ),
            NeoClientEvents::NEO_POST_REQUEST_SEND => array(
                'onSuccessfulRequest', 30
            )
        );
    }

    public function __construct(ConnectionManager $connectionManager, CommandManager $commandManager, GuzzleHttpClient $httpClient, LoggerInterface $logger)
    {
        $this->connectionManager = $connectionManager;
        $this->commandManager = $commandManager;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function onRequestException(HttpExceptionEvent $event)
    {
        $request = $event->getRequest();
        $this->fails[$request->getConnection()] = !isset($this->fails[$request->getConnection()]) ? 1 : $this->fails[$request->getConnection()] +1;
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
                    Client::log('warning', sprintf('Connection "%s" unreacheable, using "%s"', $request->getConnection(), $master->getAlias()));
                    $this->masterUsed = true;
                    $request->setInfoFromConnection($master);
                    $request->setQueryMode('READ');
                    $event->stopPropagation();
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

    public function onPreSend(HttpClientPreSendRequestEvent $event)
    {
        $request = $event->getRequest();
        $conn = $request->getConnection();
        if (isset($this->fails[$conn]) && $this->fails[$conn] >= 5) {
            if ($request->hasQueryMode()) {
                if ($request->getQueryMode() === 'READ') {
                    if ($this->connectionManager->hasNextSlave([$conn])){
                        $next = $this->connectionManager->getNextSlave([$conn]);
                        $request->setInfoFromConnection($this->connectionManager->getConnection($next));
                    }
                }
            }
        }

        if (null !== $this->masterWriteFails && $request->getQueryMode() == 'WRITE' && $this->masterWriteFails >= 5) {
            if (null !== $this->newMasterDetected) {
                $conn = $this->connectionManager->getConnection($this->newMasterDetected);
                Client::log('debug', sprintf('Automatic Write connection change after 5 write failures on Master. Changing to the "%s" connection', $this->newMasterDetected));
                $request->setInfoFromConnection($conn);
            }
        }
    }

    public function onSuccessfulRequest(PostRequestSendEvent $event)
    {
        $request = $event->getRequest();
        $this->fails[$request->getConnection()] = null;
        $this->slavesUsed = [];
        $this->masterUsed = null;
    }

    private function detectReelectedMaster()
    {
        $slaves = $this->connectionManager->getSlaves();
        foreach ($slaves as $slave) {
            try {
                if ($this->isMaster($slave)) {
                    Client::log('debug', sprintf('Master Reelection detected, new Master is "%s".', $slave));
                    return $slave;
                }
            } catch (HttpException $e) {

            }

        }

        return null;
    }

    private function isMaster($connAlias)
    {
        $command = $this->commandManager->getCommand('neo.core_get_ha_master');
        $command->setConnection($connAlias);
        $response = $command->execute();
        if (true == $response->getBody()) {
            return true;
        }

        return false;
    }
}