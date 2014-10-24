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

namespace Neoxygen\NeoClient\Extension;

use Symfony\Component\Yaml\Yaml;

class NeoClientCoreExtension extends AbstractExtension
{
    public static function getAvailableCommands()
    {
        return Yaml::parse(__DIR__.'/../Resources/extensions/core_commands.yml');
    }

    /**
     * Convenience method that invoke the sendCypherQueryCommand
     * and passes given query and parameters arguments
     *
     * @param  string      $query              The query to send
     * @param  array       $parameters         Map of query parameters
     * @param  string|null $conn               The alias of the connection to use
     * @param  array       $resultDataContents
     * @return mixed
     */
    public function sendCypherQuery($query, array $parameters = array(), $conn = null, array $resultDataContents = array(), $writeMode = true)
    {
        $command = $this->invoke('neo.send_cypher_query', $conn);

        return $command->setArguments($query, $parameters, array('row', 'graph'))
            ->execute();
    }

    /**
     * Convenience method that returns the root of the Neo4j Api
     *
     * @param  string|null $conn The alias of the connection to use
     * @return mixed
     */
    public function getRoot($conn = null)
    {
        $command = $this->invoke('simple_command', $conn);

        return $command->execute();
    }

    /**
     * Convenience method for pinging the Connection
     *
     * @param  string|null $conn The alias of the connection to use
     * @return null        The command treating the ping will throw an Exception if the connection can not be made
     */
    public function ping($conn = null)
    {
        $command = $this->invoke('neo.ping_command', $conn);

        return $command->execute();
    }

    /**
     * Convenience method that invoke the GetLabelsCommand
     *
     * @param  string|null $conn The alias of the connection to use
     * @return mixed
     */
    public function getLabels($conn = null)
    {
        $command = $this->invoke('neo.get_labels_command', $conn);

        return $command->execute();
    }

    /**
     * Returns the registered constraints
     *
     * @param  string|null $conn
     * @return mixed
     */
    public function getConstraints($conn = null)
    {
        $command = $this->invoke('neo.get_constraints_command', $conn);

        return $command->execute();
    }

    public function createSpatialIndex()
    {
        print_r('S P A T I A L   I N D E X');
    }
}
