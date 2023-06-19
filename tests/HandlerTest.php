<?php

declare(strict_types=1);

namespace Exbico\Handler\Tests;

use DateTimeImmutable;
use Exbico\Handler\DbHandler;
use Exbico\Handler\DbHandlerConfig;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Monolog\Test\TestCase;

final class HandlerTest extends TestCase
{
    public function testDefaultConfig(): void
    {
        $connection = new ConnectionStub();

        $handler = new DbHandler($connection);

        $record = new LogRecord(
            datetime: new DateTimeImmutable('2023-06-12 12:00:00'),
            channel : 'test',
            level   : Level::Debug,
            message : 'Test message!',
            context : ['testKey' => 'test value'],
            extra   : [],
        );

        $handler->handle($record);

        $log = $connection->getLastLog();

        $this->assertNotNull($log);
        $this->assertEquals('log_debug', $log['table']);
        $this->assertEquals('DEBUG', $log['level']);
        $this->assertEquals('Test message!', $log['message']);
        $this->assertEquals($record->datetime->format(DATE_ATOM), $log['datetime']);
        $this->assertEquals('{"testKey":"test value"}', $log['context']);
        $this->assertEquals(null, $log['extra']);
    }

    public function testUnhandledLevel(): void
    {
        $connection = new ConnectionStub();

        $handler = new DbHandler($connection, new DbHandlerConfig(emergency: null));

        $record = new LogRecord(
            datetime: new DateTimeImmutable('2023-06-12 12:00:00'),
            channel : 'test',
            level   : Level::Emergency,
            message : 'Test message!',
            context : ['testKey' => 'test value'],
            extra   : [],
        );

        $handler->handle($record);

        $this->assertNull($connection->getLastLog());
    }
}
