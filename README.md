# GraphAware Neo4j PHP Client

## An Enterprise Grade Client for Neo4j

[![Build Status](https://travis-ci.org/graphaware/neo4j-php-client.svg?branch=master)](https://travis-ci.org/graphaware/neo4j-php-client)
[![Latest Stable Version](https://poser.pugx.org/graphaware/neo4j-php-client/v/stable.svg)](https://packagist.org/packages/graphaware/neo4j-php-client)
[![Total Downloads](https://poser.pugx.org/neoxygen/neoclient/downloads.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![License](https://poser.pugx.org/neoxygen/neoclient/license.svg)](https://packagist.org/packages/graphaware/neo4j-php-client)

## Introduction

Neo4j-PHP-Client is the most advanced and flexible [Neo4j](http://neo4j.com) Client for PHP. 

### What is Neo4j?

Neo4j is a transactional, open-source graph database. A graph database manages data in a connected data structure, capable of representing any kind of data in a very accessible way. Information is stored in nodes and relationships connecting them, both of which can have arbitrary properties. To learn more visit [What is a Graph Database](http://neo4j.com/developer/graph-database/)?

### Key features

* Supports multiple connections
* Built-in and automatic support for *Neo4j Enterprise HA Master-Slave Mode* with auto slaves fallback
* Fully extensible (You can create your own extensions)

#### Neo4j Version Support

| **Version** | **Tested**  |
|-------------|-------------|
| <= 2.2.6    |   No        |
| >= 2.2.6    |   Yes       |
| 2.2         |   Yes       |
| 2.3         |   Yes       |

#### Neo4j Feature Support

| **Feature**          | **Supported?** |
|----------------------|----------------|
| Auth                 |  Yes           |
| Remote Cypher        |  Yes           |
| Transactions         |  Yes           |
| High Availability    |  Yes           |
| Embedded JVM support |  No            |
| Binary Protocol      |  Yes           |

### Requirements

* PHP 5.6+
* A Neo4j database (minimum version 2.2.6)

### Getting Help

You can:

 * Check out an [example application built with NeoClient](https://github.com/neo4j-contrib/developer-resources/tree/gh-pages/language-guides/php/neoclient)
 * [Ask a question on StackOverflow](http://stackoverflow.com/questions/ask?tags=graphaware,php,neo4j)
 * For bugs, please feel free to create a [new issue on GitHub](https://github.com/graphaware/neo4j-php-client/issues/new)
 
## Installation and basic usage

### Installation

Add the library to your composer dependencies :

```bash
composer require graphaware/neo4j-php-client
```

Require the composer autoloader, configure your connection by providing a connection alias and your connection settings :

```php
<?php

require_once 'vendor/autoload.php';

use GraphAware\Neo4j\Client\ClientBuilder;

$client = ClientBuilder::create()
    ->addConnection('default', 'http://neo4j:password@localhost')
    ->build();
```

You're now ready to connect to your database.

NB: The build method will process configuration settings and return you a `Client` instance.

### Basic Usage

#### Sending a Cypher Query

```php
$client->run("CREATE (n:Person)");
```

#### Sending a Cypher Query with parameters

```php
$client->run("CREATE (n:Person) SET n += {infos}", ['infos' => ['name' => 'Ales', 'age' => 34]]);
```

#### Reading a Result

```php
$result = $client->run("MATCH (n:Person) RETURN n";
// a result contains always a collection (array) of Record objects

// get all records
$records = $result->getRecords();

// get the first or (if expected only one) the only record

$record = $result->getRecord();
```

A `Record` object contains the values of one record from your Cypher query :

```php
$query = "MATCH (n:Person)-[:FOLLOWS]->(friend) RETURN n.name, collect(friend) as friends";
$result = $client->run($query);

foreach ($result->getRecords() as $record) {
    echo sprintf('Person name is : %s and has %d number of friends', $record->value('name'), count($record->value('friends'));
}
```


### License

The library is released under the MIT License, refer to the LICENSE file.




