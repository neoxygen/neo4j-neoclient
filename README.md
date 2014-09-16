# NeoClient

## A PHP HttpClient for the Neo4j ReST API with MultiDB Support

[![Build Status](https://travis-ci.org/neoxygen/neo4j-neoclient.svg?branch=master)](https://travis-ci.org/neoxygen/neo4j-neoclient)

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
$client->getVersion();

// Returns (string) 2.1.4
```

#### openTransaction | Opens a new http transaction
```
$client->openTransaction();
```

```json
{"commit":"http://localhost:7474/db/data/transaction/32/commit","results":[],"transaction":{"expires":"Tue, 16 Sep 2014 21:56:29 +0000"},"errors":[]}
```

... More to be written

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

