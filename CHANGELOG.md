## 4.0

* new Statement object


## 3.3.1 & 3.3.2

* Fixed some bugs concerning live transactions in respect to the send Multiple in the scope of the tx

## 3.3.0

* Introduced optional new response formatter by GraphAware

## 3.2.0

* Added Schema Index and Unique Constraint objects and methods for it in the Client
* Uses now GraphUnit for Integration testing

## 3.1.2

* Fixed docblock in live transaction

## 3.1.1

* When using getTable, if relationships should be present, their data will be returned in the corresponding row's column

## 3.1.0

* Added the possibility to add have headers to define query mode
* Fixed builder preventing setting a custom formatter class via non-yml way

## 2.2.7

* Added method for enabling the HA Mode without YAML Config

## 2.1.17

* Added useful methods for by passing relationships in node objects

* `getConnectedNodes`
* `getConnectedNode`


## 2.1.5

* Fixed node internal ID not being casted to int
* added a parameter `removeIndexIfExist` defaulted to false in `createUniqueConstraint` which will drop automatically 
the index when creating the constraint

```php
$client->createUniqueConstraint('Repository', 'name', true);
```

* added `changePassword` method for Neo4j 2.2M04+

```php
$client->changePassword('myUser', 'newPassword');
```

## 2.1

### Bug fixes

* getProperty() on relationship was returning always true
* sendMultiple was not converting empty arrays to maps in the Request json body

### Features

* added Prepared Transaction instance for handling multiple statements in one commit

## 2.0

* The bootstrap process has been changed
* The `getVersion` method has been replaced by the `getNeo4jVersion()` method.
* `listIndex` returns now an array of indexed properties for the given label
* new method `listIndexes` returning an array `label => [$properties]`


## 1.5
- Added a ResponseFormatter for handling API responses

## 1.4

- Added a fallback mode for defining fallback connections in case of main connection failure

## 1.3

- ChangeFeed Module command added to core
- HttpClient send method takes now a fifth parameter `query` to add query strings to the http request

## 1.2

- Auth Extension commands added to core

- HttpClient receives now the ConnectionManager, it allows further improvement to provide fallback connections
or duplication of commands