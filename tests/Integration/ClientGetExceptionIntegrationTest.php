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

use GraphAware\Neo4j\Client\ClientBuilder;

class ClientGetExceptionIntegrationTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionHandling()
    {
        $client = ClientBuilder::create()
            ->addConnection('default', 'bolt://localhost')
            ->build();

        $result = $client->run("CREATE (n:Cool");

    }
}