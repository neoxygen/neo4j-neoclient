# NeoClient

## A PHP HttpClient for the Neo4j ReST API with MultiDB Support

[![Build Status](https://travis-ci.org/neoxygen/neo4j-neoclient.svg?branch=master)](https://travis-ci.org/neoxygen/neo4j-neoclient)
[![Latest Stable Version](https://poser.pugx.org/neoxygen/neoclient/v/stable.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![Total Downloads](https://poser.pugx.org/neoxygen/neoclient/downloads.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![Latest Unstable Version](https://poser.pugx.org/neoxygen/neoclient/v/unstable.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![License](https://poser.pugx.org/neoxygen/neoclient/license.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/bac840f0-7b30-4206-a0e0-c6f4ca320077/big.png)](https://insight.sensiolabs.com/projects/bac840f0-7b30-4206-a0e0-c6f4ca320077)

+[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/neoxygen/neo4j-neoclient?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

### Requirements

* PHP 5.4+
* A running Neo4j database

### Installation

Add the library to your `composer.json` file :

```json
{
    "require": {
        "neoxygen/neoclient": "~2.0.*"
    }
}
```

Require the composer autoloader and load your configuration file and build your connection by providing a connection alias and your connection
settings :

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

#### getVersion | Returns the Neo4j version of the current connection

```php
$version = $client->getVersion();

// Returns (string) 2.1.5
```

#### Sending a Cypher Query

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


### Handling Graph Results

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

### Authenticated connection

If you are using the `authenticated-extension`, you can specify to use the authMode for the connection and provide your username
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

### Core Commands for the `Authentication extension`

#### listUsers | List the users registered in the connection authentication extension

```php
$client->listUsers();
```

```json
{"john:password":"RW"}
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
{"john:password":"RO"}
```

#### removeUser | Removes a user from the extension

```php
$client->removeUser('user', 'password');
```

```json
OK
```

### Working with multiple connections

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

### Fallback connections

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

### Creating a Commands Extension

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

### Production settings

The library uses a Dependency Injenction Container and service files definitions, while this provide flexibilty and
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

### Extra Commands

#### GraphAware ChangeFeed Module

Details about the module here : https://github.com/graphaware/neo4j-changefeed

#### getChangeFeed | Returns the last tracks of changes made to the graph

```php
$changes = $client->getChangeFeed();

// Or with parameters (uuid, limit, moduleId, connectionAlias)
$changes = $client->getChangeFeed(null, 10);
```

```json
[{"uuid":"6f166230-3d0b-11e4-8f99-84383559c16e","timestamp":1410808004563,"changes":["Created node (:TestLabel)"]},{"uuid":"86c3d3b0-3ac0-11e4-8f99-84383559c16e","timestamp":1410555929707,"changes":["Created node (:Looob)"]},{"uuid":"93358400-3abf-11e4-8f99-84383559c16e","timestamp":1410555521088,"changes":["Created node (:UriahHeep)"]},{"uuid":"b4e4fa20-398b-11e4-8f99-84383559c16e","timestamp":1410423292610,"changes":["Created node (:Person {type: hello})"]},{"uuid":"8adf60d0-398b-11e4-8f99-84383559c16e","timestamp":1410423222109,"changes":["Created node (:MyLabel {green: yel})","Created relationship (:Product:Track {name: hello})-[:LOVESPRIMES_AT]->(:MyLabel {green: yel})","Created node (:Product:Track {name: hello})"]},{"uuid":"6f10b200-398b-11e4-8f99-84383559c16e","timestamp":1410423175456,"changes":["Created node (:MyLabel {green: yellow})","Created relationship (:Product {name: hello})-[:LOVES_TO]->(:MyLabel {green: yellow})","Created node (:Product {name: hello})"]}]airbook:commandr ikwattro$ php test.php
[{"uuid":"6f166230-3d0b-11e4-8f99-84383559c16e","timestamp":1410808004563,"changes":["Created node (:TestLabel)"]}]
```

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

Integration Tests :

Run `vendor/bin/phpunit`

Unit tests:

Run `vendor/bin/phpspec -f`

#### openTransaction | Opens a new http transaction
```php
$transaction = $client->openTransaction();
```

```json
{"commit":"http://localhost:7474/db/data/transaction/32/commit","results":[],"transaction":{"expires":"Tue, 16 Sep 2014 21:56:29 +0000"},"errors":[]}
```

#### rollBackTransaction | Roll backs a transaction
```php
$transactionId = 59;
$rollback = $client->rollbackTransaction($transactionId);
```

```json
{"results":[],"errors":[]}
```

#### pushToTransaction | Add a statement to a given transaction

```php
$transactionId = 60;
$query = 'MATCH (n) RETURN count(n)';
$result = $client->pushToTransaction($transactionId, $query);
```

```json
{"results":[{"columns":["count(n)"],"data":[{"row":[24]}]}],"errors":[]}
```

#### commitTransaction | Add a statement to a given transaction

Query is here optional, as you can commit a transaction without adding a cypher statement.

```php
$transactionId = 60;
$query = 'MATCH (n) RETURN count(n)';
$result = $client->commitTransaction($transactionId, $query);
```

```json
{"results":[{"columns":["count(n)"],"data":[{"row":[24]}]}],"errors":[]}
```

### Using the Transaction Manager

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



