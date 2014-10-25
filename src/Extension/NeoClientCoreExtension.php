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
use Neoxygen\NeoClient\Transaction\Transaction;

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

    /**
     * Returns the list of indexed properties for a given Label
     *
     * @param  string      $label
     * @param  string|null $conn
     * @return mixed
     */
    public function listIndex($label, $conn = null)
    {
        $command = $this->invoke('neo.list_index_command', $conn);
        $command->setArguments($label);
        $response = $command->execute();
        if (!is_array($response)){
            $indexes = json_decode($response, true);
        } else {
            $indexes = $response;
        }

        $propertiesIndexed = [];
        foreach ($indexes as $index){
            foreach ($index['property_keys'] as $key){
                $propertiesIndexed[] = $key;
            }
        }

        return [
            $label => $propertiesIndexed
        ];
    }

    /**
     * Checks if a property is indexed for a given label
     *
     * @param  string      $label
     * @param  string      $propertyKey
     * @param  string|null $conn
     * @return bool
     */
    public function isIndexed($label, $propertyKey, $conn = null)
    {
        $indexes = $this->listIndex($label, $conn);
        if (in_array($propertyKey, $indexes[$label])) {
            return true;
        }

        return false;
    }

    /**
     * Convenience method that invoke the GetVersionCommand
     *
     * @param  string|null $conn The alias of the connection to use
     * @return mixed
     */
    public function getVersion($conn = null)
    {
        $command = $this->invoke('neo.get_neo4j_version', $conn);

        return $command->execute();
    }



    /**
     * Convenience method that invoke the OpenTransactionCommand
     *
     * @param  string|null $conn The alias of the connection to use
     * @return mixed
     */
    public function openTransaction($conn = null)
    {
        return $this->invoke('neo.open_transaction', $conn)
            ->execute();
    }

    /**
     * Creates a new Transaction Handler
     *
     * @param  string|null $conn The connection alias
     * @return Transaction
     */
    public function createTransaction($conn = null)
    {
        $transaction = new Transaction($conn, $this);

        return $transaction;
    }

    public function createSpatialIndex()
    {
        print_r('S P A T I A L   I N D E X');
    }
}
