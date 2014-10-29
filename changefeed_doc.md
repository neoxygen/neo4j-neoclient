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