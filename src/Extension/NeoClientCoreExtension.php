<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Extension;

use Neoxygen\NeoClient\Exception\Neo4jException;
use Neoxygen\NeoClient\Exception\CypherException;
use Neoxygen\NeoClient\Schema\Index;
use Neoxygen\NeoClient\Schema\UniqueConstraint;
use Neoxygen\NeoClient\Transaction\PreparedTransaction;
use Symfony\Component\Yaml\Yaml;
use Neoxygen\NeoClient\Transaction\Transaction;
use Neoxygen\NeoClient\Request\Response;
use Neoxygen\NeoClient\Client;
use GuzzleHttp\Psr7\Response as PsrResponse;

class NeoClientCoreExtension extends AbstractExtension
{
    public static function getAvailableCommands()
    {
        return Yaml::parse(file_get_contents(__DIR__.'/../Resources/extensions/core_commands.yml'));
    }

    /**
     * Convenience method that returns the root of the Neo4j Api.
     *
     * @param string|null $conn The alias of the connection to use
     *
     * @return mixed
     */
    public function getRoot($conn = null)
    {
        $command = $this->invoke('simple_command', $conn);
        $httpResponse = $command->execute();

        return $this->handleHttpResponse($httpResponse);
    }

    /**
     * Convenience method that invoke the GetVersionCommand.
     *
     * @param string|null $conn The alias of the connection to use
     *
     * @return mixed
     */
    public function getNeo4jVersion($conn = null)
    {
        $command = $this->invoke('neo.get_neo4j_version', $conn);
        $httpResponse = $command->execute();

        $response = $this->handleHttpResponse($httpResponse);

        return $response->getBody()['neo4j_version'];
    }

    /**
     * Convenience method for pinging the Connection.
     *
     * @param string|null $conn The alias of the connection to use
     */
    public function ping($conn = null)
    {
        $command = $this->invoke('neo.ping_command', $conn);
        $httpResponse = $command->execute();

        return $this->handleHttpResponse($httpResponse);
    }

    /**
     * Convenience method that invoke the sendCypherQueryCommand
     * and passes given query and parameters arguments.
     *
     * @param string      $query      The query to send
     * @param array       $parameters Map of query parameters
     * @param string|null $conn       The alias of the connection to use
     * @param string|null $queryMode  The mode of the query, could be WRITE or READ
     *
     * @return mixed
     */
    public function sendCypherQuery($query, array $parameters = array(), $conn = null, $queryMode = null)
    {
        $command = $this->invoke('neo.send_cypher_query', $conn);
        if (!is_string($query)) {
            throw new CypherException('You need to send a Cypher query as a string. Usage: "$neoclient->sendCypherQuery(string $query, array $params);"');
        }

        $httpResponse = $command->setArguments($query, $parameters, $this->resultDataContent, $queryMode)
          ->execute();

        return $this->handleHttpResponse($httpResponse);
    }

    /**
     * @param array $statements
     * @param null  $conn
     *
     * @return \Neoxygen\NeoClient\Formatter\Response
     */
    public function sendMultiple(array $statements, $conn = null, $queryMode = null)
    {
        $command = $this->invoke('neo.send_cypher_multiple', $conn);
        $command->setArguments($statements, $this->resultDataContent, $queryMode);
        $httpResponse = $command->execute();

        return $this->handleHttpResponse($httpResponse);
    }

    /**
     * @param null|string $conn Connection alias
     *
     * @return PreparedTransaction
     */
    public function prepareTransaction($conn = null)
    {
        return new PreparedTransaction($conn);
    }

    /**
     * Convenience method that invoke the GetLabelsCommand.
     *
     * @param string|null $conn The alias of the connection to use
     *
     * @return mixed
     */
    public function getLabels($conn = null)
    {
        $command = $this->invoke('neo.get_labels_command', $conn);
        $httpResponse = $command->execute();

        return $this->handleHttpResponse($httpResponse);
    }

    public function renameLabel($oldLabel, $newLabel, $conn = null)
    {
        $q = 'MATCH (old:'.$oldLabel.')
        REMOVE old:'.$oldLabel.'
        SET old :'.$newLabel.';';

        return $this->sendCypherQuery($q);
    }

    /**
     * Creates a Schema Index based on label and property.
     *
     * @param string $label
     * @param string $property
     *
     * @return \Neoxygen\NeoClient\Schema\Index
     */
    public function createSchemaIndex($label, $property, $conn = null)
    {
        $statement = 'CREATE INDEX ON :'.$label.'('.$property.')';
        $this->sendCypherQuery($statement, array(), $conn = null, self::WRITE_QUERY);

        return new Index($label, $property);
    }

    /**
     * Removes a Schema Index.
     *
     * @param \Neoxygen\NeoClient\Schema\Index $index
     * @param string|null                      $conn
     */
    public function dropSchemaIndex(Index $index, $conn = null)
    {
        $statement = 'DROP INDEX ON :'.$index->getLabel().'('.$index->getProperty().')';
        $this->sendCypherQuery($statement, array(), $conn, self::WRITE_QUERY);
    }

    /**
     * Creates an index on a label.
     *
     * @param string       $label
     * @param string|array $property
     *
     * @return bool
     *
     * @deprecated will be removed in 4.0
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
     * Returns the list of indexed properties for a given Label.
     *
     * @param string      $label
     * @param string|null $conn
     *
     * @return array
     */
    public function listIndex($label, $conn = null)
    {
        $command = $this->invoke('neo.list_index_command', $conn);
        $command->setArguments($label);
        $httpResponse = $command->execute();

        $response = $this->handleHttpResponse($httpResponse);
        $propertiesIndexed = [];
        foreach ($response->getBody() as $index) {
            foreach ($index['property_keys'] as $key) {
                $propertiesIndexed[] = $key;
            }
        }
        if ($response instanceof \GraphAware\NeoClient\Formatter\Response) {
            return new \GraphAware\NeoClient\Formatter\Response(new PsrResponse(200, array(), json_encode($propertiesIndexed)));
        }
        $response->setBody($propertiesIndexed);

        return $response;
    }

    /**
     * Drops an index on a label.
     *
     * @param string $label
     * @param string $property
     *
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
     * @param array       $labels
     * @param string|null $conn
     *
     * @return Response
     * @deprecetad Will be removed in 4.0
     */
    public function listIndexes(array $labels = array(), $conn = null)
    {
        if (empty($labels)) {
            $labels = $this->getLabels($conn)->getBody();
        }
        $indexes = [];
        foreach ($labels as $label) {
            $res = $this->listIndex($label, $conn);
            $indexs = $res->getBody();
            $indexes[$label] = $indexs;
        }

        if (!isset($res)){
            if ($this->newFormatModeEnabled) {
                $res = new \GraphAware\NeoClient\Formatter\Response(new PsrResponse(200));
            } else {
                $res = new Response(new PsrResponse(200));
            }
        }

        if ($res instanceof \GraphAware\NeoClient\Formatter\Response) {
            return new \GraphAware\NeoClient\Formatter\Response(new PsrResponse(200, [], json_encode($indexes)));
        }

        $response = new Response($res->getRaw());
        $response->setBody($indexes);

        return $response;
    }

    /**
     * Returns the schema indexes live in the database.
     *
     * @param null $conn
     *
     * @return \Neoxygen\NeoClient\Schema\Index[]
     */
    public function getSchemaIndexes($conn = null)
    {
        $indx = [];
        $labels = $this->getLabels($conn)->getBody();
        foreach ($labels as $label) {
            $indexes = $this->listIndex($label, $conn)->getBody();
            foreach ($indexes as $property) {
                $indx[] = new Index($label, $property);
            }
        }

        return $indx;
    }

    /**
     * Checks if a property is indexed for a given label.
     *
     * @param string      $label
     * @param string      $propertyKey
     * @param string|null $conn
     *
     * @return bool
     */
    public function isIndexed($label, $propertyKey, $conn = null)
    {
        $indexes = $this->listIndex($label, $conn)->getBody();
        if (in_array($propertyKey, $indexes)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the registered constraints.
     *
     * @param string|null $conn
     *
     * @return mixed
     */
    public function getUniqueConstraints($conn = null)
    {
        $command = $this->invoke('neo.get_constraints_command', $conn);
        $httpResponse = $command->execute();

        $responseO = $this->handleHttpResponse($httpResponse);
        $response = $responseO->getBody();
        $constraints = [];
        foreach ($response as $constraint) {
            foreach ($constraint['property_keys'] as $key) {
                $constraints[$constraint['label']][] = $key;
            }
        }
        if ($responseO instanceof \GraphAware\NeoClient\Formatter\Response) {
            $msg = new PsrResponse(200, array(), json_encode($constraints));
            return new \GraphAware\NeoClient\Formatter\Response($msg);
        }

        $responseO->setBody($constraints);

        return $responseO;
    }

    /**
     * Create a unique constraint on a label.
     *
     * @param string       $label
     * @param string|array $property
     *
     * @return bool
     */
    public function createUniqueConstraint($label, $property, $removeIndexIfExist = false)
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
            try {
                $this->sendCypherQuery($statement);
            } catch (Neo4jException $e) {
                if (true === $removeIndexIfExist && 8000 === $e->getCode() && !is_array($property)) {
                    $this->dropIndex($label, $property);

                    return $this->createUniqueConstraint($label, $property);
                }
                throw($e);
            }
        }

        return true;
    }

    /**
     * @param $label
     * @param $property
     * @param bool $transformIndexInConstraint
     * @param null $conn
     *
     * @return \Neoxygen\NeoClient\Schema\UniqueConstraint
     *
     * @throws \Neoxygen\NeoClient\Exception\Neo4jException
     */
    public function createSchemaUniqueConstraint($label, $property, $transformIndexInConstraint = false, $conn = null)
    {
        $q = 'CREATE CONSTRAINT ON (n:'.$label.') ASSERT n.'.$property.' IS UNIQUE';
        try {
            $this->sendCypherQuery($q);

            return new UniqueConstraint($label, $property);
        } catch (Neo4jException $e) {
            if (true === $transformIndexInConstraint && 8000 === $e->getCode()) {
                // do remove constraint
            }

            throw $e;
        }
    }

    /**
     * Drops a Uniqueness constraint from the Schema.
     *
     * @param \Neoxygen\NeoClient\Schema\UniqueConstraint $constraint
     * @param null                                        $conn
     */
    public function dropSchemaUniqueConstraint(UniqueConstraint $constraint, $conn = null)
    {
        $q = 'DROP CONSTRAINT ON (n:'.$constraint->getLabel().') ASSERT n.'.$constraint->getProperty().' IS UNIQUE';
        $this->sendCypherQuery($q);
    }

    /**
     * Returns a collection of Uniqueness constraints live in the schema.
     *
     * @param null $conn
     *
     * @return \Neoxygen\NeoClient\Schema\UniqueConstraint[]
     */
    public function getSchemaUniqueConstraints($conn = null)
    {
        $command = $this->invoke('neo.get_constraints_command', $conn);
        $httpResponse = $command->execute();

        $responseO = $this->handleHttpResponse($httpResponse);
        $response = $responseO->getBody();
        $constraints = [];
        foreach ($response as $constraint) {
            foreach ($constraint['property_keys'] as $key) {
                $constraints[] = new UniqueConstraint($constraint['label'], $key);
            }
        }

        return $constraints;
    }

    /**
     * Drops a unique constraint on a label.
     *
     * @param string       $label
     * @param string|array $property
     *
     * @return bool
     */
    public function dropUniqueConstraint($label, $property)
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
     * Creates a new Transaction Handler.
     *
     * @param string|null $conn The connection alias
     *
     * @return Transaction
     */
    public function createTransaction($conn = null, $queryMode = Client::NEOCLIENT_QUERY_MODE_WRITE)
    {
        $transaction = new Transaction($conn, $this, $queryMode);

        return $transaction;
    }

    /**
     * Convenience method that invoke the OpenTransactionCommand.
     *
     * @param string|null $conn The alias of the connection to use
     *
     * @return mixed
     */
    public function openTransaction($conn = null, $queryMode = self::WRITE_QUERY)
    {
        $command = $this->invoke('neo.open_transaction', $conn);
        $command->setArguments($queryMode);
        $httpResponse = $command->execute();

        return $this->handleHttpResponse($httpResponse);
    }

    /**
     * Convenience method that invoke the RollBackTransactionCommand.
     *
     * @param int         $id   The id of the transaction
     * @param string|null $conn The alias of the connection to use
     *
     * @return mixed
     */
    public function rollBackTransaction($id, $conn = null)
    {
        $response = $this->invoke('neo.rollback_transaction', $conn)
          ->setTransactionId($id)
          ->execute();

        return $this->handleHttpResponse($response);
    }

    /**
     * Convenience method that invoke the PushToTransactionCommand
     * and passes the query and parameters as arguments.
     *
     * @param int         $transactionId The transaction id
     * @param string      $query         The query to send
     * @param array       $parameters    Parameters map of the query
     * @param string|null $conn          The alias of the connection to use
     *
     * @return mixed
     */
    public function pushToTransaction($transactionId, $query, array $parameters = array(), $conn = null, $queryMode = Client::NEOCLIENT_QUERY_MODE_WRITE)
    {
        $httpResponse = $this->invoke('neo.push_to_transaction', $conn)
          ->setArguments($transactionId, $query, $parameters, $this->resultDataContent, $queryMode)
          ->execute();

        return $this->handleHttpResponse($httpResponse);
    }

    public function pushMultipleToTransaction($transactionId, array $statements, $conn = null)
    {
        $response = $this->invoke('neo.push_multiple_to_transaction', $conn)
          ->setArguments($transactionId, $statements, $this->resultDataContent)
          ->execute();

        return $this->handleHttpResponse($response);
    }

    /**
     * Convenience method that commit the transaction
     * and passes the optional query and parameters as arguments.
     *
     * @param int         $transactionId The transaction id
     * @param string|null $query         The query to send
     * @param array       $parameters    Parameters map of the query
     * @param string|null $conn          The alias of the connection to use
     *
     * @return mixed
     */
    public function commitTransaction($transactionId, $query = null, array $parameters = array(), $conn = null, $queryMode = self::WRITE_QUERY)
    {
        $response = $this->invoke('neo.commit_transaction', $conn)
          ->setArguments($transactionId, $query, $parameters, $this->resultDataContent, $queryMode)
          ->execute();

        return $this->handleHttpResponse($response);
    }

    public function changePassword($user, $newPassword, $conn = null)
    {
        $response = $this->invoke('neo.core_change_password', $conn)
          ->setArguments($user, $newPassword)
          ->execute();

        return $response;
    }

    /**
     * @param string|null $connectionAlias
     *
     * @return mixed
     */
    public function listUsers($connectionAlias = null)
    {
        $response = $this->invoke('neo.list_users', $connectionAlias)
          ->execute();

        return $this->handleHttpResponse($response);
    }

    /**
     * @param string      $user
     * @param string      $password
     * @param bool        $readOnly
     * @param string|null $connectionAlias
     *
     * @return mixed
     */
    public function addUser($user, $password, $readOnly = false, $connectionAlias = null)
    {
        $response = $this->invoke('neo.add_user', $connectionAlias)
          ->setReadOnly($readOnly)
          ->setUser($user)
          ->setPassword($password)
          ->execute();

        return $this->handleHttpResponse($response);
    }

    /**
     * @param string      $user
     * @param string      $password
     * @param string|null $connectionAlias
     *
     * @return mixed
     */
    public function removeUser($user, $password, $connectionAlias = null)
    {
        $response = $this->invoke('neo.remove_user', $connectionAlias)
          ->setUser($user)
          ->setPassword($password)
          ->execute();

        return $this->handleHttpResponse($response);
    }

    /**
     * Convenience method for working with replication
     * Sends a read only query.
     *
     * @param string $query
     * @param array  $parameters
     *
     * @return mixed
     */
    public function sendReadQuery($query, array $parameters = array())
    {
        return $this->sendCypherQuery($query, $parameters, $this->getReadConnection()->getAlias(), self::READ_QUERY);
    }

    /**
     * Convenience method for working with replication.
     *
     * @param string $query
     * @param array  $parameters
     *
     * @return mixed
     */
    public function sendWriteQuery($query, array $parameters = array())
    {
        return $this->sendCypherQuery($query, $parameters, $this->getWriteConnection()->getAlias(), self::WRITE_QUERY);
    }

    /**
     * Get the connection alias of the Master Connection.
     *
     * @return string
     */
    public function getWriteConnectionAlias()
    {
        return $this->getWriteConnection()->getAlias();
    }

    /**
     * Get the connection alias of the first Slave connection.
     *
     * @return string
     */
    public function getReadConnectionAlias()
    {
        return $this->getReadConnection()->getAlias();
    }

    /**
     * @param string|null $conn
     *
     * @return mixed
     */
    public function checkHAMaster($conn = null)
    {
        $response = $this->invoke('neo.core_get_ha_master', $conn)
          ->execute();

        return $this->handleHttpResponse($response);
    }

    /**
     * @param string|null $conn
     *
     * @return mixed
     */
    public function checkHASlave($conn = null)
    {
        $response = $this->invoke('neo.core_get_ha_slave', $conn)
          ->execute();

        return $this->handleHttpResponse($response);
    }

    /**
     * @param string|null $conn
     *
     * @return mixed
     */
    public function checkHAAvailable($conn = null)
    {
        $response = $this->invoke('neo.core_get_ha_available', $conn)
          ->execute();

        return $this->handleHttpResponse($response);
    }

    /**
     * Retrieve paths between two nodes.
     *
     * @param array       $startNodeProperties
     * @param array       $endNodeProperties
     * @param int|null    $depth
     * @param string|null $direction
     * @param string|null $conn
     *
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
                $startNPattern .= $key.': {start_'.$key.'}';
                if ($value instanceof \DateTime) {
                    $value = $value->format('Ymdhis');
                }
                $parameters['start_'.$key] = $value;
                if ($i < $propsCount - 1) {
                    $startNPattern .= ', ';
                }
                ++$i;
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
                $endNPattern .= $key.': {end_'.$key.'}';
                if ($value instanceof \DateTime) {
                    $value = $value->format('Ymdhis');
                }
                $parameters['end_'.$key] = $value;
                if ($i < $propsCount - 1) {
                    $endNPattern .= ', ';
                }
                ++$i;
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
            $q .= ' WHERE id(start) = {startNodeId}';
            $parameters['startNodeId'] = $startNodeProperties['id'];
        }

        if (isset($endNodeProperties['id'])) {
            if (isset($startNodeProperties['id'])) {
                $q .= ' AND ';
            }
            $q .= ' WHERE id(end) = {endNodeId}';
            $parameters['endNodeId'] = $endNodeProperties['id'];
        }

        $q .= ' RETURN p';

        $response = $this->sendCypherQuery($q, $parameters, $conn, array('graph', 'row'));

        return $this->handleHttpResponse($response);
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
