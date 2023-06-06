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

$log = new Logger('name');

$handlerMy = new DbHandler(
    levels:    [Logger::DEBUG],
    dsn:       'mysql:dbname=exbico;host=127.0.0.1',
    username:  'username',
    password:  'password',
    tableName: 'debug_logs'
);
$log->pushHandler($handlerMy);

$handlerPg = new DbHandler(
    levels:    [400, Logger::CRITICAL, 550, 600],
    dsn:       'pgsql:dbname=lead_service_log;host=127.0.0.1',
    username:  'username',
    password:  'password',
    options:   [...],
    tableName: 'errors_logs'
);
$log->pushHandler($handlerPg);
```

You must create table with fields:
* `level` - varchar
* `message` - varchar|text
* `context` - text|json|jsonb
