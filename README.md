# Akiban Web Service Client for PHP

Akiban Server has a [REST API][rest_api]. This client allows easy
interaction with this API.

# Installation

Create `composer.json` file in your project root:

```
{
    "require": {
        "guzzle/guzzle": "~3.1.1"
    }
}
```

Then download `composer.phar` and run the install command:

```
curl -s http://getcomposer.org/installer | php && ./composer.phar install
```

# Quick Examples

```php
<?php

require 'vendor/autoload.php';

use Akiban\AkibanClient;

// Instantiate an Akiban client
$client = AkibanClient::factory(array('scheme' => 'https',
                                      'username' => 'user',
                                      'password' => 'pass',
                                      'hostname' => 'localhost'));

// Retrieve an entity named 'hopes' with an ID of 3
// returns a JSON document representing the entity
echo $client->getEntity('hopes', 3);

// Execute a SQL query
// results are returned in JSON format
echo $client->executeSqlQuery('select * from hopes');

// Execute multiple SQL statements in 1 transaction
// results for each statement are returned as a field
// in a JSON document
$queries = array(
  'select max(id) from hopes',
  'select * from hopes'
);
echo $client->executeMultipleSqlQueries($queries);
```

[rest_api]: https://akiban.readthedocs.org/en/latest/service/restapireference.html
