<?php

namespace Exbico\Handler;

use JsonException;
use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use Monolog\LogRecord;
use PDO;
use PDOStatement;
use Throwable;

final class DbHandler implements HandlerInterface
{
    private PDOStatement|false $statement = false;

    /**
     * @param Level[] $levels
     * @param PDO $connection
     * @param string $tableName
     */
    public function __construct(
        private array $levels,
        private readonly PDO $connection,
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

    public function isHandling(LogRecord $record): bool
    {
        return in_array($record->level, $this->levels, true);
    }

    public function handle(LogRecord $record): bool
    {
        if ($this->statement !== false && $this->isHandling($record)) {
            try {
                $this->statement->execute(
                    [
                        'level'    => $record->level->getName(),
                        'message'  => $record->message,
                        'datetime' => $record->datetime->format(DATE_ATOM),
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
     * @param LogRecord $record
     * @return string|null
     * @throws JsonException
     */
    private function getRecordContext(LogRecord $record): ?string
    {
        if (!empty($record->context)) {
            return json_encode($record->context, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } else {
            return null;
        }
    }

    /**
     * @param LogRecord $record
     * @return string|null
     * @throws JsonException
     */
    private function getRecordExtra(LogRecord $record): ?string
    {
        if (!empty($record->extra)) {
            return json_encode($record->extra, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } else {
            return null;
        }
    }
}
