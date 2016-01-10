<?php

/**
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Tests\Integration;

use GraphAware\Neo4j\Client\Client;
use GraphAware\Neo4j\Client\ClientBuilder;

class ClientSetupIntegrationTest extends \PHPUnit_Framework_TestCase
{
    public function testClientSetupWithOneConnection()
    {
        $client = ClientBuilder::create()
            ->addConnection('default', 'bolt://localhost')
            ->build();

        $this->assertInstanceOf(Client::class, $client);
    }
}