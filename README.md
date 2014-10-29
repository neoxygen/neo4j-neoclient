# NeoClient

## A PHP HttpClient for the Neo4j ReST API with MultiDB Support

[![Build Status](https://travis-ci.org/neoxygen/neo4j-neoclient.svg?branch=master)](https://travis-ci.org/neoxygen/neo4j-neoclient)
[![Latest Stable Version](https://poser.pugx.org/neoxygen/neoclient/v/stable.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![Total Downloads](https://poser.pugx.org/neoxygen/neoclient/downloads.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![Latest Unstable Version](https://poser.pugx.org/neoxygen/neoclient/v/unstable.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![License](https://poser.pugx.org/neoxygen/neoclient/license.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/bac840f0-7b30-4206-a0e0-c6f4ca320077/big.png)](https://insight.sensiolabs.com/projects/bac840f0-7b30-4206-a0e0-c6f4ca320077)

+[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/neoxygen/neo4j-neoclient?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)


This is the documentation for the upcoming 2.0 branch. For the doc of the 1.6.*, checkout the 1.6 branch.

## Introduction

NeoClient is the most advanced and flexible [Neo4j](http://neo4j.com) Client for PHP. 

### Key features

* Support multiple connections
* Built-in and automatic support for *Neo4j Enterprise HA Master-Slave Mode* with auto slaves fallback
* Built-in mini HA Mode for Neo4j Community Edition
* Fully extensible (You can create your own extensions)

### Requirements

* PHP 5.4+
* A Neo4j database

## Installation and basic usage

### Installation

Add the library to your `composer.json` file :

```json
{
    "require": {
        "neoxygen/neoclient": "~2.0@dev"
    }
}
```

Require the composer autoloader, configure your connection by providing a connection alias and your connection settings :

```php
<?php

require_once 'vendor/autoload.php';

use Neoxygen\NeoClient\ClientBuilder;

$client = ClientBuilder::create()
    ->addConnection('default','http','localhost',7474)
    ->build();
```

You're now ready to connect to your database.

If you use default database settings in a local environment (meaning _http://localhost:7474_), you can use the handy `addDefaultLocalConnection` method :

```php
$client = ClientBuilder::create()
    ->addDefaultLocalConnection()
    ->build();
```

The build method will process configuration settings and return you a `Client` instance.

### Usage

You have now full access to the database.


#### getRoot | Returns the root endpoint

```php
$root = $client->getRoot();
```

```php
Array
    (
        [management] => http://localhost:7474/db/manage/
        [data] => http://localhost:7474/db/data/
    )
```

Note: As the library provide full support for working with multiple databases, each method explained in the documentation can take 
a `$conn` parameter which you can use to define on which connection you want to execute the method. The default connection will be used when
the parameter is not set.

For more information on how to set up multiple connections, read the `Multiple connections` section of the documentation.


#### getVersion | Returns the Neo4j version of the current connection

```php
$version = $client->getVersion();

// Returns (string) 2.1.5
```

## Sending Cypher Queries

In order to send a Cypher Query, you need to pass the query as a string, and an optional array of paramaters as arguments :

```php

$q = 'MATCH (n) RETURN count(n)';
$response = $client->sendCypherQuery($q);
```

````php
Array
(
    [results] => Array
        (
            [0] => Array
                (
                    [columns] => Array
                        (
                            [0] => count(n)
                        )

                    [data] => Array
                        (
                            [0] => Array
                                (
                                    [row] => Array
                                        (
                                            [0] => 1
                                        )
......                                        
```

Handling such response format is not really practical and boring. You can ask the client to format the response in a pretty way and have
this format available to you :

```
$client = ClientBuilder::create()
    ->addDefaultLocalConnection()
    ->setAutoFormatResponse(true)
    ->build();
```

To get the pretty format :

```php

$q = 'MATCH (n:Actor) RETURN n.name';
$client->sendCypherQuery($q);

$result = $client->getRows();
```

```
Array
(
    [n.name] => Array
        (
            [0] => Keanu Reeves
            [1] => Laurence Fishburne
            [2] => Carrie-Anne Moss
        )

)

```
## Labels, Indexes and Constraints Management

### Managing labels

The library provide handy methods for managing your labels :


#### getLabels | Returns the labels indexed in the database

```php
$labels = $client->getLabels();
```

```php
[ "UriahHeep", "MyLabel", "Version", "Customer", "TestLabel" ]
```

#### renameLabel | Fetch all nodes for that label and rename the label of the nodes

Note that depending on the amount of nodes for the given label, this can take some time.

Call the `renameLabel` method and pass the old label name and the new label name as arguments :

```php
$client->renameLabel('Person', 'User');
```

### Managing Indexes and Constraints

Indexes and Constraints management is also an easy task

#### createIndex | Creates an index for a label/property pair

```php
$client->createIndex('Person', 'email');
```

#### listIndex | List indexed properties for a given label

```php
$client->listIndex('Person');
```

Returns you an array of indexed properties for the given label

#### listIndexes | List indexed properties for given labels or all labels

```php
$personAndUserIndexes = $client->listIndexes(['Person','User']);

$allIndexes = $client->listIndexes();
```

Returns you an array of indexed properties by the form `['Label' => ['prop1','prop2']]`.

#### dropIndex | Drop an index for a given label/property pair

```php
$client->dropIndex('Person','email');
```

#### isIndexed | Checks whether or not a given label/property pair is indexed

```php
$client->isIndexed('User','username');
```

Returns true or false

#### createUniqueConstraint | Create a uniqueness constraint for a given label/property pair

```php
$client->createUniqueConstraint('User','username');
```

#### dropUniqueConstraint | Drop a uniqueness constraint for a given label/property pair

```php
$client->dropUniqueConstraint('User','username');
```

#### getUniqueConstraints | Returns all the uniqueness constraints by label

```php
$constraints = $client->getConstraints();
```

Returns `['User' => ['username','email'], 'Movie' => ['imdb_id']]`


## Handling Graph Results

The Response Formatter will format graph results in a pretty format of nodes and relationships objects.

If you've setup the `autoFormatResponse` configuration value, when a graph result is available, a graph representation
is available for you :

```php
$query = 'MATCH (a:Actor)-[r]-(m:Movie) RETURN *';
$client->sendCypherQuery($query);

// Getting the graph Result
$result = $client->getResult();

// The raw response is still available :
$response = $client->getResponse();

// Getting all nodes

$nodes = $result->getNodes();

// Getting all movie nodes from the result
$movies = $result->getNodes('Movie');

// Getting all movie and Actor nodes from the result

$moviesAndActors = $result->getNodes(['Movie','Actor']);
// Returns you a collection of nodes objects

// If you want to group the nodes by labels, you can pass true as second argument to the getNodes method

$moviesAndActors = $result->getNodes(['Movie','Actor'], true);
// Returns an array with labels as keys ['Movie' => ['NodeObject1', 'NodeObject2']]


// Getting only one movie (returns in fact the first element of an array, but is handy when you expect only one node
$movie = $result->getSingleNode('Movie');

// Working with the relationships

$movie = $result->getSingleNode('Movie');
$actors = $movie->getRelationships('ACTS_IN');
// Or you may want to specify direction
$actors = $movie->getRelationships('ACTS_IN', 'IN');

// If you need only one relationship :
$actor = $movie->getSingleRelationship('ACTS_IN');

// Getting node/relationships properties

// Getting one property
$actor = $result->getSingleNode('Actor');
$name = $actor->getProperty('name');

// Getting all properties
$props = $actor->getProperties();

// Getting a set of properties
$props = $actor->getProperties(['name', 'date_of_birh']);

// Getting the node internal Id (Id of the Neo4j database)

$id = $actor->getId();

// Getting a node by id in the Result set

$node = $result->getNodeById(34);

// Counting Nodes And Relationships

$nbNodes = $result->getNodesCount();
$nbRels = $result->getRelationshipsCount();
```

## Transaction Management

The library comes with a Transaction Manager removing you the burden of parsing commit urls and transaction ids.

Usage is straightforward :

```php
$transaction = $client->createTransaction();
$transaction->pushQuery('MERGE (n:User {id: 123}) RETURN n');
$transaction->pushQuery('MATCH (n) RETURN count(n)');
$transaction->commit();

// Other methods :
$transaction->rollback();
$transaction->getLastResult // Returns the result of the last transaction statements
$transaction->getResults() // Returns the results of all the statements
```

Note that a commited or a rolled back transaction will not accept pushQuery calls anymore.

## Working with multiple connections

### Define multiple connections

You can work with as many connections you want :

```yaml
connections:
  default:
    scheme: http
    host: localhost
    port: 7474
  testdb:
    scheme: http
    host: testserver.dev
    port: 7475
```

When calling commands, you can specify to which connection the command has to be executed by passing the connection alias as argument :

```php
$client->getRoot('default');
$client->sendCypherQuery('MATCH (n) RETURN count(n) as total', array(), 'testdb');
```

## HA (High-Availibilty) Mode for Neo4j Enterprise

The library provide a powerful system for handling the HA Mode of Neo4j available in Neo4j Enterprise.

The convention is to send write queries to the master, and read queries to slaves.

To enable the HA Mode and defining which connections are master or slave, you need to add some method call during the build process of the
client :

```php

$client = ClientBuilder::create()
    ->addConnection('server1', 'http', '193.147.213.3', 7474)
    ->addConnection('server2', 'http', '193.147.213.4', 7474)
    ->addConnection('server3', 'http', '193.147.213.7', 7474)
    ->setMasterConnection('server1') // Define the Master Connection by providing the connection alias
    ->setSlaveConnection('server2') // Idem for slave connections
    ->setSlaveConnection('server3')
    ->build();
```

Your configuration is now set. The client has convenience methods for HA usage, respectively `sendReadQuery` and `sendWriteQuery`.

Automatically, write queries will be executed against the `master` connection, while `read` queries against slave connections.

If a slave is no more reachable, it will automatically check if other slaves are configured. If yes it will attempt to send the query again 
to the other slave connections.

```php

$client->sendWriteQuery('MERGE (n:User {firstname: "Chris"})'); // Will be sent to the "server1" connection

$client->sendReadQuery('MATCH (n:User) RETURN n'); // Will be sent to the "server2" connection
```

NB: The above methods do not take the `$conn` argument as the choice of the connection is done in the library internals.

Note: You can always retrieve the Master and the first Slave connection alias from the client if you want to specify them when using other commands :

```php

$client->getRoot($client->getWriteConnectionAlias()); // Will be run against the master connection

$client->listLabels($client->getReadConnectionAlias()); // Will be run agains the first found slave connection
```

Please also note, that when using the *Transaction Manager*, all queries will be run against the same connection. *Transaction*  instances 
are bounded to one and only connection.

## Secured connections

### Authenticated connection

If you are using the `authenticated-extension` or using [GrapheneDB](http://graphenedb.com) instance, you can specify to use the authMode for the connection and provide your username
and password :

```yaml
connections:
  default:
    scheme: http
    host: localhost
    port: 7474
    auth: true
    user: user
    password: s3Cr3T
```

Your password will automatically encoded in base64 for the Authorization.

### Convenience methods for the `Authentication extension`

#### listUsers | List the users registered in the connection authentication extension

```php
$client->listUsers();
```

```json
{"john"}
```

#### addUser | Adds a user to the extensions

```php
$client->addUser('John', 'password');
```

```json
OK
```

The third argument of the `addUser` method is the `readOnly` parameter, default to false

```
$client->addUser('john', 'password', true);
```

```json
OK
{"john"}
```

#### removeUser | Removes a user from the extension

```php
$client->removeUser('user', 'password');
```

```json
OK
```
    


## Mini HA for Neo4j Community

Fallback connections feature provide a mini HA mode when you use the Community Edition. If you are using the Enterprise edition 
we recommend that you set up the built-in `HA Mode` feature of this library.

When working with multiple connections, you may work with a main db and a backup db, and define the backup db as
a fallback in case of connection failure with the main db.

Configuring a fallback connection in your config file (define a connection key with the fallback connection to use as
value :

```yaml
connections:
  default:
    scheme: http
    host: localhost
    port: 7474
  testdb:
    scheme: http
    host: testserver.dev
    port: 7475

fallback:
  default: testdb
```

For each command, in case of connection failure, the http client will check if a fallback is defined and use it.

If you have loggers settled up, an `alert` entry will be logged to avert you of the connection failure.

## Events & Logging

### Event Listeners

You can add listeners to hook into the built-in event system, for all list of all available events, look inside the
`NeoEvents.php` file.

A listener can be a \Closure instance, an object implementing an __invoke method,
a string representing a function, or an array representing an object method or a class method.

Event listeners are currently not configurable with the yaml file, it will come soon...

```php
$client
    ->loadConfiguration('file')
    ->addEventListener('foo.action', function (Event $event));
```

### Logging

You can add your logging system or ask the library to use the default built-in logging mechanism (currently only stream and ChromePHP
are supported).

If you integrate your own logging, he must be compatible with the PSR-3 standard.

```php
// Adding your own logging
$client->setLogger('app', MyLogger); // My Logger must implement Psr\Log\LoggerInterface

// asking the default

$client->createDefaultStreamLogger('name', '/path/to/your/log/file.log', 'debug');
$client->createDefaultChromePHPLogger('app', 'debug');
```

## Extending NeoClient


### Creating your own commands

You can extend the library by creating your own commands.

Create your `Command` class, this class must extend `Neoxygen\NeoClient\Command\AbstractCommand` and must implement
the `execute` method.

By extending the AbstractCommand class, you have access to the http client, and also the connection alias that is used
when invoking the command.

The best way to execute a command is by calling the `send` request of the HttpClient and passing the `method`, `path`,
`body` and `connectionAlias` arguments :

```php
<?php

namespace Acme;

use Neoxygen\NeoClient\Command\AbstractCommand;

/**
* Class that is used to get the extensions listed in the API
*/
class MyCommand extends AbstractCommand
{
    public function execute()
    {
        $method = 'GET';
        $path = '/db/data/extensions';

        // The arguments for the send method of the http client are
        // $method, $path, $body = null, $connectionAlias = null

        return $this->httpClient->send($method, $path, null, $this->connection);
    }
}
```

Then you have to register your command when building the client by passing an alias for your command and the class name :

```yaml
connections:
  default:
    scheme: http
    host: localhost
    port: 7474
  testdb:
    scheme: http
    host: testserver.dev
    port: 7475

custom_commands:
    custom_get_extensions:
        class: My\Custom\Command\Class
```

Then to use your command, just use the invoke method of the client :

```php
$command = $client->invoke('custom_get_extensions');
$extensions = $command->execute();
print_r($extensions);
```

### Creating an Extension

When you have a lot of commands, it may be good to create a command extension. Creating a command extension is quite simple :

You need to create a class that implements the `Neoxygen\NeoClient\Extension\NeoClientExtensionInterface`, and you have to
implement the `getAvailableCommands` method that return an array of command aliases bounded to command classes :

```php

use Neoxygen\NeoClient\Extension\NeoClientExtensionInterface;

class MyExtension implements NeoClientExtensionInterface
{
    public static function getAvailableCommands()
    {
        return array(
            'custom_get_extensions' => 'My\Command\Class',
            'custom_other_exec' => 'My\Other\Class'
            );
    }
}
```

And then register your extension when building the client by giving an alias and the class of your extension :

```yaml
connections:
  default:
    scheme: http
    host: localhost
    port: 7474
  testdb:
    scheme: http
    host: testserver.dev
    port: 7475

extensions:
  my_extension:
    class: My\Extension\Clas
```

## Production settings

The library uses a Dependency Injenction Container and service files definitions, while this provide full flexibility and
robust code, this comes at a price.

By providing a cache path where the container and all the configuration can be dumped, you'll have the best of both worlds.

```yaml
connections:
  default:
    scheme: http
    host: localhost
    port: 7474
  testdb:
    scheme: http
    host: testserver.dev
    port: 7475

fallback:
  default: testdb

cache:
  enable: true
  cache_path: /dev/project/cache/
```

Don't forget to add full permissions to the cache path : `chmod -R 777 your/cache/path` and also to empty the cache dir when
you do changes to your configuration.

### Configuration Reference

### YAML

```yaml
connections:
  default:
    scheme: http
    host: localhost
    port: 7474
  testdb:
    scheme: http
    host: testserver.dev
    port: 7475

fallback:
  default: testdb

cache:
  enabled: true
  cache_path: /dev/project/cache

custom_commands:
  my_command:
    class: My\Command\Class

extensions:
  my_extension:
    class: My\Extension\Class

default_result_data_content: ['row','graph','rest'] #default to "row"
```

### PHP

```php

$client = new Client();
$client
  ->addConnection('default','http','localhost',7474,true,'user','password')
  ->addConnection('backupdb','http','testserver',7475)
  ->setFallbackConnection('default', 'backupdb')
  ->enableCache('my/cache/path')
  ->registerCommand('my_command', 'My\Command\Path')
  ->registerExtension('my_extension', 'My\Extension\Class\Path')
  ->setLogger('my_logger', new MyLogger())
  ->createDefaultStreamLogger('main', '/path/to/log/', 'debug')
  ->createDefaultChromePHPLogger('other_log')
  ->setDefaultResultDataContent(array('row', 'graph', 'rest')
  ->build();
```

---

### License

The library is released under the MIT License, refer to the LICENSE file.

### Tests

To run the test suite, you need to copy the `tests/database_settings.yml.dist` to `tests/database_settings.yml`, as it will
create nodes to a real database.

Run `vendor/bin/phpunit`




