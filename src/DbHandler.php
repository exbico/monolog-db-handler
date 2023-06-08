<?php

namespace Exbico\Handler;

use Exception;
use JsonException;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use PDO;
use PDOStatement;
use Throwable;

/**
 * @phpstan-import-type Record from Logger
 * @phpstan-import-type Level from Logger
 */
final class DbHandler implements HandlerInterface
{
    private PDOStatement|false $statement = false;

    /**
     * @param PDO $connection
     * @param int[] $levels
     * @param string $tableName
     * @phpstan-param Level[] $levels
     */
    public function __construct(
        private array $levels,
        private PDO $connection,
        string $tableName = 'log',
    ) {
        try {
            $this->statement = $this->connection->prepare(
                sprintf(
                    'INSERT INTO %s (level, message, datetime, context, extra) '
                    . 'VALUES (:level, :message, :datetime, :context, :extra)',
                    $tableName,
                ),
            );
        } catch (Throwable) {
        }
    }

    /**
     * @param array $record
     * @return bool
     * @phpstan-param array{level: Level} $record
     */
    public function isHandling(array $record): bool
    {
        return in_array($record['level'], $this->levels, true);
    }

    /**
     * @param array $record
     * @return bool
     * @phpstan-param Record $record
     */
    public function handle(array $record): bool
    {
        if ($this->statement !== false && $this->isHandling($record)) {
            $level = $this->getRecordLevel($record);
            try {
                $this->statement->execute(
                    [
                        'level'    => Logger::getLevelName($level),
                        'message'  => $this->getRecordMessage($record),
                        'datetime' => $this->getRecordTime($record),
                        'context'  => $this->getRecordContext($record),
                        'extra'    => $this->getRecordExtra($record),
                    ],
                );
            } catch (Throwable) {
            }
        }
        return false;
    }

    public function handleBatch(array $records): void
    {
        foreach ($records as $record) {
            $this->handle($record);
        }
    }

    public function close(): void
    {
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
        } else {
            return null;
        }
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
        } else {
            return null;
        }
    }
}
