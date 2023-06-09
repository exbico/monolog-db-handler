MONOLOG DB HANDLER
=================

[![Latest Stable Version](https://poser.pugx.org/exbico/monolog-db-handler/v/stable)](https://packagist.org/packages/exbico/monolog-db-handler) [![Total Downloads](https://poser.pugx.org/exbico/monolog-db-handler/downloads)](https://packagist.org/packages/exbico/monolog-db-handler) [![License](https://poser.pugx.org/drtsb/yii2-seo/license)](https://packagist.org/packages/exbico/monolog-db-handler)

## INSTALLATION
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
composer require exbico/monolog-db-handler
```
or add

```
"exbico/monolog-db-handler": "*"
```

to the require section of your application's `composer.json` file.

## Basic Usage

```php
<?php

use Monolog\Logger;

use Exbico\Handler\DbHandler;

$log = new Logger('name',[new DbHandler(
    levels:    [Logger::ERROR, Logger::CRITICAL, Logger::ALERT, Logger::EMERGENCY],
    connection: new PDO(dsn: 'pgsql:dbname=foo;host=127.0.0.1', username: 'root', password: null),
    tableName: 'errors_logs'
)]);

$log->pushHandler(
    new DbHandler(
        levels:    [Logger::DEBUG],
        connection: new PDO(dsn: 'mysql:dbname=bar;host=127.0.0.1', username: 'root', password: null),
        tableName: 'debug_logs'
    )
);

$log->pushHandler($handlerPg);
```

You must create table with fields:
* `level` - varchar
* `message` - varchar|text
* `datetime` - datetime
* `context` - text|json|jsonb
* `extra` - text|json|jsonb
