# NeoClient

## A PHP HttpClient for the Neo4j ReST API with MultiDB Support

[![Build Status](https://travis-ci.org/neoxygen/neo4j-neoclient.svg?branch=master)](https://travis-ci.org/neoxygen/neo4j-neoclient)
[![Latest Stable Version](https://poser.pugx.org/neoxygen/neoclient/v/stable.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![License](https://poser.pugx.org/neoxygen/neoclient/license.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/bac840f0-7b30-4206-a0e0-c6f4ca320077/big.png)](https://insight.sensiolabs.com/projects/bac840f0-7b30-4206-a0e0-c6f4ca320077)

### Installation

Add the library to your `composer.json` file :

```json
{
    require: {
        "neoxygen/neoclient": "dev-master"
    }
}
```
### Configuration

Require the composer autoloader and load your configuration :

```php
<?php

require_once 'vendor/autoload.php';

use Neoxygen\NeoClient\Client;

$client = new Client()
    ->addConnection('default', 'http', 'localhost', 7474)
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
$query = 'MATCH (n) RETURN count(n);
$result = $client->pushToTransaction($transactionId, $query);
```

```json
{"results":[{"columns":["count(n)"],"data":[{"row":[24]}]}],"errors":[]}
```

#### Authenticated connection

If you are using the `authenticated-extension`, you can specify to use the authMode for the connection and provide your username
and password :

```php
$client->addConnection('default', 'http', 'localhost', 7474, true, 'user', 'password');
```

Your password will automatically encoded in base64 for the Authorization.

#### Working with multiple connections

You can work with as many connections you want :

```php
$client
    ->addConnection('default', 'http', 'localhost', 7474)
    ->addConnection('testdb', 'https', 'testserver.dev', 7575)
    ->build();
```

When calling commands, you can specify to which connection the command has to be executed by passing the connection alias as argument :

```php
$client->getRoot('default');
$client->sendCypherQuery('MATCH (n) RETURN count(n) as total', array(), 'testdb');
```

#### Adding Event Listeners

You can add listeners to hook into the built-in event system, for all list of all available events, look inside the
`NeoEvents.php` file.

A listener can be a \Closure instance, an object implementing an __invoke method,
a string representing a function, or an array representing an object method or a class method.

```php
$client->addEventListener('foo.action', function (Event $event));
```

#### Logging

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

#### Creating your own commands :

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

```php
$client = new Client()
    ->addConnection('default', 'http', 'localhost', 7474)
    ->registerCommand('custom_get_extensions', 'My\Command\Class\Name')
    ->build();
```

Then to use your command, just use the invoke method of the client :

```php
$command = $client->invoke('custom_get_extensions');
$extensions = $command->execute();
print_r($extensions);
```

#### Creating a Commands Extension

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

```php

$client->registerExtension('my_commands', 'My\Extension\Class')
    ->build();
```


---


## License

The library is released under the MIT License, refer to the LICENSE file.

## Tests

To run the test suite, you need to copy the `tests/database_settings.yml.dist` to `tests/database_settings.yml`, as it will
create nodes to a real database.

Integration Tests :

Run `vendor/bin/phpunit`

Unit tests:

Run `vendor/bin/phpspec -f`



