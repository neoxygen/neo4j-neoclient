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

        $httpResponse = $command->setArguments($query, $parameters, array('row', 'graph'))
            ->execute();

        return $this->handleHttpResponse($httpResponse);
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
        $httpResponse = $command->execute();

        return $this->handleHttpResponse($httpResponse);
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

        $httpResponse = $command->execute();

        return $this->handleHttpResponse($httpResponse);
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

        $httpResponse = $command->execute();
        $responseObject = $this->handleHttpResponse($httpResponse);

        return $responseObject->getResponse();

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

        $httpResponse = $command->execute();
        $responseObject = $this->handleHttpResponse($httpResponse);

        return $responseObject->getResponse();
    }

    /**
     * Returns the list of indexed properties for a given Label
     *
     * @param  string      $label
     * @param  string|null $conn
     * @return array
     */
    public function listIndex($label, $conn = null)
    {
        $command = $this->invoke('neo.list_index_command', $conn);
        $command->setArguments($label);
        $httpResponse = $command->execute();
        $response = $this->handleHttpResponse($httpResponse)->getResponse();
        $propertiesIndexed = [];
        foreach ($response as $index) {
            foreach ($index['property_keys'] as $key) {
                $propertiesIndexed[] = $key;
            }
        }

        return $propertiesIndexed;
    }

    /**
     * @param array $labels
     * @param string|null $conn
     * @return array
     */
    public function listIndexes(array $labels = array(), $conn = null)
    {
        if (empty($labels)) {
            $labels = $this->getLabels($conn);
        }
        $indexes = [];
        foreach ($labels as $label) {
            $indexs = $this->listIndex($label, $conn);
            $indexes[$label] = $indexs;
        }

        return $indexes;
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
        if (in_array($propertyKey, $indexes)) {

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
    public function getNeo4jVersion($conn = null)
    {
        $command = $this->invoke('neo.get_neo4j_version', $conn);
        $httpResponse = $command->execute();

        return $this->handleHttpResponse($httpResponse);
    }

    /**
     * Convenience method that invoke the OpenTransactionCommand
     *
     * @param  string|null $conn The alias of the connection to use
     * @return mixed
     */
    public function openTransaction($conn = null)
    {
        $command = $this->invoke('neo.open_transaction', $conn);
        $httpResponse = $command->execute();

        $response = $this->handleHttpResponse($httpResponse);

        return $response->getResponse();
    }

    /**
     * Creates a new Transaction Handler
     *
     * @param  string|null $conn The connection alias
     * @return Transaction
     */
    public function createTransaction($conn = null)
    {
        $transaction = new Transaction($conn, $this, $this->responseFormatterClass);

        return $transaction;
    }

    /**
     * Convenience method that invoke the RollBackTransactionCommand
     *
     * @param  int         $id   The id of the transaction
     * @param  string|null $conn The alias of the connection to use
     * @return mixed
     */
    public function rollBackTransaction($id, $conn = null)
    {
        return $this->invoke('neo.rollback_transaction', $conn)
            ->setTransactionId($id)
            ->execute();
    }

    /**
     * Convenience method that invoke the PushToTransactionCommand
     * and passes the query and parameters as arguments
     *
     * @param  int         $transactionId The transaction id
     * @param  string      $query         The query to send
     * @param  array       $parameters    Parameters map of the query
     * @param  string|null $conn          The alias of the connection to use
     * @return mixed
     */
    public function pushToTransaction($transactionId, $query, array $parameters = array(), $conn = null, array $resultDataContents = array(), $writeMode = true)
    {
        $httpResponse = $this->invoke('neo.push_to_transaction', $conn)
            ->setArguments($transactionId, $query, $parameters)
            ->execute();

        return $this->handleHttpResponse($httpResponse);
    }

    public function pushMultipleToTransaction($transactionId, array $statements, $conn = null, array $resultDataContents = array())
    {
        return $this->invoke('neo.push_multiple_to_transaction', $conn)
            ->setArguments($transactionId, $statements, $resultDataContents)
            ->execute();
    }

    /**
     * Convenience method that commit the transaction
     * and passes the optional query and parameters as arguments
     *
     * @param  int         $transactionId The transaction id
     * @param  string|null $query         The query to send
     * @param  array       $parameters    Parameters map of the query
     * @param  string|null $conn          The alias of the connection to use
     * @return mixed
     */
    public function commitTransaction($transactionId, $query = null, array $parameters = array(), $conn = null, array $resultDataContents = array(), $writeMode = true)
    {
        return $this->invoke('neo.commit_transaction', $conn)
            ->setArguments($transactionId, $query, $parameters)
            ->execute();
    }

    /**
     * @param  string|null $connectionAlias
     * @return mixed
     */
    public function listUsers($connectionAlias = null)
    {
        return $this->invoke('neo.list_users', $connectionAlias)
            ->execute();
    }

    /**
     * @param  string      $user
     * @param  string      $password
     * @param  bool        $readOnly
     * @param  string|null $connectionAlias
     * @return mixed
     */
    public function addUser($user, $password, $readOnly = false, $connectionAlias = null)
    {
        return $this->invoke('neo.add_user', $connectionAlias)
            ->setReadOnly($readOnly)
            ->setUser($user)
            ->setPassword($password)
            ->execute();
    }

    /**
     * @param  string      $user
     * @param  string      $password
     * @param  string|null $connectionAlias
     * @return mixed
     */
    public function removeUser($user, $password, $connectionAlias = null)
    {
        return $this->invoke('neo.remove_user', $connectionAlias)
            ->setUser($user)
            ->setPassword($password)
            ->execute();
    }

    /**
     * @param  string|null $uuid
     * @param  int|null    $limit
     * @param  int|null    $moduleId
     * @param  string|null $connectionAlias
     * @return mixed
     */
    public function getChangeFeed($uuid = null, $limit = null, $moduleId = null, $connectionAlias = null)
    {
        return $this->invoke('neo.changefeed', $connectionAlias)
            ->setUuid($uuid)
            ->setLimit($limit)
            ->setModuleId($moduleId)
            ->execute();
    }

    /**
     * Convenience method for working with replication
     * Sends a read only query
     *
     * @param  string      $query
     * @param  array       $parameters
     * @param  string|null $connectionAlias
     * @param  array       $resultDataContents
     * @return mixed
     */
    public function sendReadQuery($query, array $parameters = array(), $connectionAlias = null, array $resultDataContents = array())
    {
        foreach (array('MERGE', 'CREATE') as $pattern) {
            if (preg_match('/'.$pattern.'/i', $query)) {
                throw new \InvalidArgumentException(sprintf('The query "%s" contains cypher write clauses', $query));
            }
        }

        return $this->sendCypherQuery($query, $parameters, $connectionAlias, $resultDataContents, false);
    }

    /**
     * Convenience method for working with replication
     *
     * @param  string      $query
     * @param  array       $parameters
     * @param  string|null $connectionAlias
     * @param  array       $resultDataContents
     * @return mixed
     */
    public function sendWriteQuery($query, array $parameters = array(), $connectionAlias = null, array $resultDataContents = array())
    {
        return $this->sendCypherQuery($query, $parameters, $connectionAlias, $resultDataContents, true);
    }

    /**
     * Convenience method for the replication mode
     * Push a read only query to the transaction
     *
     * @param $transactionId
     * @param $query
     * @param  array $parameters
     * @param  null  $conn
     * @param  array $resultDataContents
     * @return mixed
     */
    public function pushReadQueryToTransaction($transactionId, $query, array $parameters = array(), $conn = null, array $resultDataContents = array())
    {
        foreach (array('MERGE', 'CREATE') as $pattern) {
            if (preg_match('/'.$pattern.'/i', $query)) {
                throw new \InvalidArgumentException(sprintf('The query "%s" contains cypher write clauses', $query));
            }
        }

        return $this->pushToTransaction($transactionId, $query, $parameters, $conn, $resultDataContents, false);
    }

    /**
     * Convenience method for working with the replication mode
     *
     * @param $transactionId
     * @param $query
     * @param  array $parameters
     * @param  null  $conn
     * @param  array $resultDataContents
     * @return mixed
     */
    public function pushWriteQueryToTransaction($transactionId, $query, array $parameters = array(), $conn = null, array $resultDataContents = array())
    {
        return $this->pushToTransaction($transactionId, $query, $parameters, $conn, $resultDataContents, true);
    }

    /**
     * Creates an index on a label
     *
     * @param  string       $label
     * @param  string|array $property
     * @return bool
     */
    public function createIndex($label, $property)
    {
        $statements = [];
        if (is_array($property)) {
            foreach ($property as $prop) {
                $statements[] = 'CREATE INDEX ON :'.$label.'('.$prop.')';
            }
        } else {
            $statements[] = 'CREATE INDEX ON :'.$label.'('.$property.')';
        }
        foreach ($statements as $statement) {
            $this->sendCypherQuery($statement);
        }

        return true;
    }

    /**
     * Drops an index on a label
     *
     * @param  string $label
     * @param  string $property
     * @return bool
     */
    public function dropIndex($label, $property)
    {
        $statements = [];
        if (is_array($property)) {
            foreach ($property as $prop) {
                $statements[] = 'DROP INDEX ON :'.$label.'('.$prop.')';
            }
        } else {
            $statements[] = 'DROP INDEX ON :'.$label.'('.$property.')';
        }
        foreach ($statements as $statement) {
            $this->sendCypherQuery($statement);
        }

        return true;
    }

    /**
     * Create a unique constraint on a label
     *
     * @param  string       $label
     * @param  string|array $property
     * @return bool
     */
    public function createConstraint($label, $property)
    {
        $statements = [];
        $identifier = strtolower($label);
        if (is_array($property)) {
            foreach ($property as $prop) {
                $statements[] = 'CREATE CONSTRAINT ON ('.$identifier.':'.$label.') ASSERT '.$identifier.'.'.$prop.' IS UNIQUE';
            }
        } else {
            $statements[] = 'CREATE CONSTRAINT ON ('.$identifier.':'.$label.') ASSERT '.$identifier.'.'.$property.' IS UNIQUE';
        }
        foreach ($statements as $statement) {
            $this->sendCypherQuery($statement);
        }

        return true;
    }

    /**
     * Drops a unique constraint on a label
     *
     * @param  string       $label
     * @param  string|array $property
     * @return bool
     */
    public function dropConstraint($label, $property)
    {
        $statements = [];
        $identifier = strtolower($label);
        if (is_array($property)) {
            foreach ($property as $prop) {
                $statements[] = 'DROP CONSTRAINT ON ('.$identifier.':'.$label.') ASSERT '.$identifier.'.'.$prop.' IS UNIQUE';
            }
        } else {
            $statements[] = 'DROP CONSTRAINT ON ('.$identifier.':'.$label.') ASSERT '.$identifier.'.'.$property.' IS UNIQUE';
        }
        foreach ($statements as $statement) {
            $this->sendCypherQuery($statement);
        }

        return true;
    }

    /**
     * Retrieve paths between two nodes
     *
     * @param  array       $startNodeProperties
     * @param  array       $endNodeProperties
     * @param  int|null    $depth
     * @param  string|null $direction
     * @param  string|null $conn
     * @return mixed
     */
    public function getPathBetween(array $startNodeProperties, array $endNodeProperties, $direction = null, $depth = null, $conn = null)
    {
        $this->checkPathNode($startNodeProperties);
        $this->checkPathNode($endNodeProperties);
        $parameters = [];
        $startNPattern = '(start';
        if (isset($startNodeProperties['label']) && !empty($startNodeProperties['label'])) {
            if (is_array($startNodeProperties['label'])) {
                $label = implode(':', $startNodeProperties['label']);
            } else {
                $label = ':'.$startNodeProperties['label'];
            }
            $startNPattern .= $label;
        }
        if (isset($startNodeProperties['properties']) && !empty($startNodeProperties['properties'])) {
            $startNPattern .= ' {';
            $propsCount = count($startNodeProperties['properties']);
            $i = 0;
            foreach ($startNodeProperties['properties'] as $key => $value) {
                $startNPattern .= $key . ': {start_'.$key.'}';
                if ($value instanceof \DateTime) {
                    $value = $value->format('Ymdhis');
                }
                $parameters['start_'.$key] = $value;
                if ($i < $propsCount -1) {
                    $startNPattern .= ', ';
                }
                $i++;
            }
            $startNPattern .= '}';
        }
        $startNPattern .= ')';

        $endNPattern = '(end';
        if (isset($endNodeProperties['label']) && !empty($endNodeProperties['label'])) {
            if (is_array($endNodeProperties['label'])) {
                $label = implode(':', $endNodeProperties['label']);
            } else {
                $label = ':'.$endNodeProperties['label'];
            }
            $endNPattern .= $label;
        }
        if (isset($endNodeProperties['properties']) && !empty($endNodeProperties['properties'])) {
            $endNPattern .= ' {';
            $propsCount = count($endNodeProperties['properties']);
            $i = 0;
            foreach ($endNodeProperties['properties'] as $key => $value) {
                $endNPattern .= $key . ': {end_'.$key.'}';
                if ($value instanceof \DateTime) {
                    $value = $value->format('Ymdhis');
                }
                $parameters['end_'.$key] = $value;
                if ($i < $propsCount -1) {
                    $endNPattern .= ', ';
                }
                $i++;
            }
            $endNPattern .= '}';
        }
        $endNPattern .= ')';
        if (null === $depth) {
            $rel = '[*]';
        } else {
            $d = (int) $depth;
            $rel = '[*1..'.$d.']';
        }
        switch ($direction) {
            case null:
                $in = '-';
                $out = '-';
                break;
            case 'IN':
                $in = '<-';
                $out = '-';
                break;
            case 'OUT':
                $in = '-';
                $out = '->';
                break;
            default:
                throw new \InvalidArgumentException('The direction must be IN, OUT or ALL');
        }
        $q = 'MATCH p='.$startNPattern.$in.$rel.$out.$endNPattern;
        if (isset($startNodeProperties['id'])) {
            $q .= ' WHERE id(start) = '.$startNodeProperties['id'];
        }

        if (isset($endNodeProperties['id'])) {
            if (isset($startNodeProperties['id'])) {
                $q .= ' AND ';
            }
            $q .= ' WHERE id(end) = '.$endNodeProperties['id'];
        }

        $q .= ' RETURN p';

        $response = $this->sendCypherQuery($q, $parameters, $conn, array('graph', 'row'));

        return $response->getResult();
    }

    private function checkPathNode(array $node)
    {
        if ((!isset($node['label']) || empty($node['label']))
            && (!isset($node['properties']) || empty($node['properties']))
            && (!isset($node['id']) || empty($node['id']))) {
            throw new \InvalidArgumentException('The node must contain a label or properties');
        }
    }
}
