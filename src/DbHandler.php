<?php

declare(strict_types=1);

namespace Exbico\Handler;

use Exbico\Handler\Connection\Connection;
use JsonException;
use Monolog\Handler\HandlerInterface;
use Monolog\LogRecord;
use Psr\Log\InvalidArgumentException;
use Throwable;

final class DbHandler implements HandlerInterface
{
    /**
     * @param Connection $connection
     * @param DbHandlerConfig $config
     * @param bool $bubble
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly DbHandlerConfig $config = new DbHandlerConfig(),
        private bool $bubble = true,
    ) {
    }

    public function isHandling(LogRecord $record): bool
    {
        try {
            return in_array($record->level->getName(), $this->config->getLevels(), true);
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function handle(LogRecord $record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        try {
            $this->connection->insert(
                table   : $this->config->getTable($record->level),
                level   : $record->level->getName(),
                message : $record->message,
                datetime: $record->datetime->format(DATE_ATOM),
                context : $this->getRecordContext($record),
                extra   : $this->getRecordExtra($record),
            );
        } catch (Throwable) {
            return false;
        }

        return $this->bubble === false;
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
     * @param LogRecord $record
     * @return string|null
     * @throws JsonException
     */
    private function getRecordContext(LogRecord $record): ?string
    {
        if (!empty($record->context)) {
            return json_encode($record->context, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        return null;
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
        }

        return null;
    }
}
