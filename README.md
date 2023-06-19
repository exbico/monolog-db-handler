MONOLOG DB HANDLER
=================

![ci](https://github.com/exbico/monolog-db-handler/actions/workflows/ci.yml/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/exbico/monolog-db-handler/v/stable)](https://packagist.org/packages/exbico/monolog-db-handler)
[![Total Downloads](https://poser.pugx.org/exbico/monolog-db-handler/downloads)](https://packagist.org/packages/exbico/monolog-db-handler)
[![License](https://poser.pugx.org/drtsb/yii2-seo/license)](https://packagist.org/packages/exbico/monolog-db-handler)

## INSTALLATION
The preferred way to install this extension is through [composer](http://getcomposer.org/download/):

```bash
composer require exbico/monolog-db-handler
```

## Basic Usage

```php
<?php

use Monolog\Level;
use Monolog\Logger;
use Exbico\Handler\DbHandler;
use Exbico\Handler\DbHandlerConfig;
use Exbico\Handler\Connection\PdoAdapter;

$connection = new PdoAdapter(new PDO(dsn: 'pgsql:dbname=foo;host=127.0.0.1', username: 'root', password: null));

$logger = new Logger('example',[new DbHandler(connection: $connection)]);

// You can also specify which level of messages should be logged and the table name for each level

$config = new DbHandlerConfig(
    emergency: 'log_emergency',
    alert:     'log_alert',
    critical:  'log_critical',
    error:     'log_error',
    warning:   'log_warning',
    notice:    'log_notice',
    info:      'log_info',
    debug:     null, // debug level will not be logged
);

$logger->pushHandler(new DbHandler(connection: $connection, config: $config));
```

You need to create set of required tables with the following fields:
* `level` - varchar
* `message` - varchar|text
* `datetime` - datetime
* `context` - text|json|jsonb
* `extra` - text|json|jsonb
