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
 */
class DbHandler implements HandlerInterface
{
    private const INSERT_QUERY = 'INSERT INTO %s (level, message, datetime, context, extra) '
    . 'VALUES (:level, :message, :datetime, :context, :extra)';
    private PDO $connection;
    private PDOStatement|false $statement = false;

    /**
     * @param string $dsn
     * @param string|null $username
     * @param string|null $password
     * @param array<string, mixed>|null $options
     * @param int[] $levels
     * @param string $tableName
     */
    public function __construct(
        private array $levels,
        string $dsn,
        ?string $username,
        ?string $password,
        ?array $options = null,
        private string $tableName = 'log',
    ) {
        try {
            $this->connection = new PDO($dsn, $username, $password, $options);
            $this->statement = $this->connection->prepare(sprintf(self::INSERT_QUERY, $this->tableName));
        } catch (Throwable) {
        }
    }

    public function isHandling(array $record): bool
    {
        return $this->statement !== false && in_array($record['level'], $this->levels, true);
    }

    /**
     * @param array $record
     * @return bool
     * @phpstan-param Record $record
     */
    public function handle(array $record): bool
    {
        if ($this->isHandling($record)) {
            $level = $this->getRecordLevel($record);
            try {
                /** @phpstan-ignore-next-line */
                $this->statement->execute(
                    [
                        /** @phpstan-ignore-next-line */
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
