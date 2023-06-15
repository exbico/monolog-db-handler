<?php

declare(strict_types=1);

namespace Exbico\Handler;

use Exbico\Handler\Connection\Connection;
use Exception;
use JsonException;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Log\InvalidArgumentException;
use Throwable;

/**
 * @phpstan-import-type Record from Logger
 * @phpstan-import-type Level from Logger
 */
final class DbHandler implements HandlerInterface
{
    private DbHandlerConfig $config;

    /**
     * @param Connection $connection
     * @param ?DbHandlerConfig $config
     */
    public function __construct(
        private Connection $connection,
        ?DbHandlerConfig $config = null,
    ) {
        $this->config = $config ?? new DbHandlerConfig();
    }

    /**
     * @param array $record
     * @return bool
     * @phpstan-param array{level: Level} $record
     */
    public function isHandling(array $record): bool
    {
        try {
            return in_array(Logger::getLevelName($record['level']), $this->config->getLevels(), true);
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * @param array $record
     * @return bool
     * @phpstan-param Record $record
     */
    public function handle(array $record): bool
    {
        if ($this->isHandling($record)) {
            try {
                $level = Logger::getLevelName($this->getRecordLevel($record));
                $this->connection->insert(
                    table   : $this->config->getTable($level),
                    level   : $level,
                    message : $this->getRecordMessage($record),
                    datetime: $this->getRecordTime($record),
                    context : $this->getRecordContext($record),
                    extra   : $this->getRecordExtra($record),
                );
            } catch (Throwable) {
                return false;
            }
        }
        return true;
    }

    public function handleBatch(array $records): void
    {
        foreach ($records as $record) {
            $this->handle($record);
        }
    }

    public function close(): void
    {
        $this->connection->close();
    }

    /**
     * @param array<string, mixed> $record
     * @return int
     * @phpstan-param Record $record
     * @phpstan-return Level
     */
    private function getRecordLevel(array $record): int
    {
        return $record['level'];
    }

    /**
     * @param array<string, mixed> $record
     * @return string
     * @phpstan-param Record $record
     */
    private function getRecordMessage(array $record): string
    {
        return $record['message'];
    }

    /**
     * @param array<string, mixed> $record
     * @return string
     * @throws Exception
     * @phpstan-param Record $record
     */
    private function getRecordTime(array $record): string
    {
        return $record['datetime']->format(DATE_ATOM);
    }

    /**
     * @param array<string, mixed> $record
     * @return string|null
     * @throws JsonException
     * @phpstan-param Record $record
     */
    private function getRecordContext(array $record): ?string
    {
        if (!empty($record['context'])) {
            return json_encode($record['context'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $record
     * @return string|null
     * @throws JsonException
     * @phpstan-param Record $record
     */
    private function getRecordExtra(array $record): ?string
    {
        if (!empty($record['extra'])) {
            return json_encode($record['extra'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        return null;
    }
}
