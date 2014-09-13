# NeoClient

## A PHP HttpClient for the Neo4j ReST API with MultiDB Support

### Installation

Add the library to your `composer.json` file :

```json
{
    require: {
        "neoxygen/neoclient": "*"
    }
}
```

Create a neoclient configuration file :

You'll need to create a `yaml` configuration file, in the near future you will be able to generate the file with cli :

```yaml
neoclient:
    connections:
        default:
            scheme: http
            host: localhost
            port: 7474
```

Require the composer autoloader and load your configuration :

```php
<?php

require_once 'vendor/autoload.php';

use Neoxygen\NeoClient\NeoClient;

NeoClient::getServiceContainer()
    ->loadConfiguration(__DIR__.'/neoclient.yml')
    ->build();
```

That's it ! If you have a running database with the above connection settings, you'll be able to access it with simple commands :

```php
print_r(NeoClient::getRoot());

// Will output
#Array
#(
#    [management] => http://localhost:7474/db/manage/
#    [data] => http://localhost:7474/db/data/
#)
```
