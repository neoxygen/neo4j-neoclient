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