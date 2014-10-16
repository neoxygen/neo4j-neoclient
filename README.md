# NeoClient

## A PHP HttpClient for the Neo4j ReST API with MultiDB Support

[![Build Status](https://travis-ci.org/neoxygen/neo4j-neoclient.svg?branch=master)](https://travis-ci.org/neoxygen/neo4j-neoclient)
[![Latest Stable Version](https://poser.pugx.org/neoxygen/neoclient/v/stable.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![Latest Unstable Version](https://poser.pugx.org/neoxygen/neoclient/v/unstable.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![Latest Unstable Version](https://poser.pugx.org/neoxygen/neoclient/v/unstable.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![License](https://poser.pugx.org/neoxygen/neoclient/license.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/bac840f0-7b30-4206-a0e0-c6f4ca320077/big.png)](https://insight.sensiolabs.com/projects/bac840f0-7b30-4206-a0e0-c6f4ca320077)

## Documentation

* [Installation](https://github.com/neoxygen/neo4j-neoclient#installation)
* [Configuration](https://github.com/neoxygen/neo4j-neoclient#configuration)
* [Usage](https://github.com/neoxygen/neo4j-neoclient#usage)
* [The Response formatter](https://github.com/neoxygen/neo4j-neoclient#the-response-formatter)
* [Authenticated Connection](https://github.com/neoxygen/neo4j-neoclient#authenticated-connection)
* [Working with multiple connections](https://github.com/neoxygen/neo4j-neoclient#working-with-multiple-connections)
* [Fallback connections](https://github.com/neoxygen/neo4j-neoclient#fallback-connections)
* [Event Listeners](https://github.com/neoxygen/neo4j-neoclient#event-listeners)
* [Logging](https://github.com/neoxygen/neo4j-neoclient#logging)
* [Creating your own commands](https://github.com/neoxygen/neo4j-neoclient#creating-your-own-commands)
* [Creating a Commands Extension](https://github.com/neoxygen/neo4j-neoclient#creating-a-commands-extension)
* [Production settings](https://github.com/neoxygen/neo4j-neoclient#production-settings)
* [Extra commands](https://github.com/neoxygen/neo4j-neoclient#extra-commands)
* [Configuration Reference](https://github.com/neoxygen/neo4j-neoclient#configuration-reference)

### Installation

Add the library to your `composer.json` file :

```json
{
    "require": {
        "neoxygen/neoclient": "~1.6"
    }
}
```
### Configuration

Configuration can be done with a `yaml` configuration file, if you want to configure the library with proceduaral PHP,
check the [Configuration Reference](https://github.com/neoxygen/neo4j-neoclient#configuration-reference) section.

Create for e.g. a `neoconfig.yml` file at the root of your project and start defining your connection settings :

```yaml
connections:
  default_db:
    scheme: http
    host: localhost
    port: 7474
```

Require the composer autoloader and load your configuration file:

```php
<?php

require_once 'vendor/autoload.php';

use Neoxygen\NeoClient\Client;

$client = new Client()
    ->loadConfigurationFile('/path/to/your_project/neoclient.yml')
    ->build();
```

You're now ready to connect to your database.

### Usage

The library use the Command pattern, there are core basic commands available :

#### getRoot | Returns the root endpoint

```php
$root = $client->getRoot();
```

```json

 {
    "management" : "http://localhost:7474/db/manage/",
    "data" : "http://localhost:7474/db/data/"
 }
```

#### getLabels | Returns the labels indexed in the database

```php
$labels = $client->getLabels();
```

```json
[ "UriahHeep", "MyLabel", "Version", "Customer", "TestLabel" ]
```

#### getVersion | Returns the Neo4j version of the current connection

```php
$version = $client->getVersion();

// Returns (string) 2.1.4
```

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

### The Response Formatter

The library comes with a handy response formatter, using it is currently optional, in the future the choice will have
to be made in the configuration.

The formatter works with the graph resultDataContent, so don't forget to specify it when doing your queries.

The following examples are based on the Neo4j movie database example :

```php
use Neoxygen\NeoClient\Formatter\ResponseFormatter;

$formatter = new Formatter();
$query = 'MATCH p=(a:Actor)-[]-(m:Movie) RETURN p';
$response = $client->sendCypherQuery($q, array(), null, array('graph'));

$result = $formatter->format($response);

// Getting all nodes

$nodes = $result->getNodes();

// Getting all movie nodes from the respone
$movies = $result->getNodesByLabel('Movie');

// Getting only one movie (returns in fact the first element of an array, but is handy when you expect only one node
$movie = $result->getSingleNode('Movie');

// Checking for errors

if ($result->hasErrors() {
    // ...
}

// Working with the relationships

$movie = $result->getSingleNode('Movie');
$actors = $movie->getRelationships('ACTS_IN');
// Or you may want to specify direction
$actors = $movie->getRelationships('ACTS_IN', 'IN');

// If you need only one relationship :
$actor = $movie->getSingleRelationship('ACTS_IN');
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



