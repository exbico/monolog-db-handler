<?php

declare(strict_types=1);

namespace Exbico\Handler\Connection;

interface Connection
{
    /**
     * @param string $table
     * @param string $level
     * @param string $message
     * @param string $datetime
     * @param string|null $context
     * @param string|null $extra
     * @return void
     * @throws ConnectionException
     */
    public function insert(
        string $table,
        string $level,
        string $message,
        string $datetime,
        ?string $context,
        ?string $extra,
    ): void;

    public function close(): void;
}
