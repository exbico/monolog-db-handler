<?php

declare(strict_types=1);

namespace Exbico\Handler\Tests;

use Exbico\Handler\Connection\Connection;

/**
 * @phpstan-type Log array{
 *    table: string,
 *    level: string,
 *    message: string,
 *    datetime: string,
 *    context: string|null,
 *    extra: string|null,
 * }
 */
final class ConnectionStub implements Connection
{
    /** @var list<Log> $logs */
    private array $logs = [];

    public function insert(
        string $table,
        string $level,
        string $message,
        string $datetime,
        ?string $context,
        ?string $extra,
    ): void {
        $this->logs[] = [
            'table'    => $table,
            'level'    => $level,
            'message'  => $message,
            'datetime' => $datetime,
            'context'  => $context,
            'extra'    => $extra,
        ];
    }

    public function close(): void
    {
    }

    /**
     * @return array|null
     * @phpstan-return Log|null
     */
    public function getLastLog(): ?array
    {
        return $this->logs[array_key_last($this->logs)] ?? null;
    }
}
