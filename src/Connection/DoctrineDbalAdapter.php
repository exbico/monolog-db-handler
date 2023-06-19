<?php

declare(strict_types=1);

namespace Exbico\Handler\Connection;

use Doctrine\DBAL\Statement;
use Throwable;

final class DoctrineDbalAdapter implements Connection
{
    /** @var array<string, Statement> $statements */
    private array $statements = [];

    public function __construct(private \Doctrine\DBAL\Connection $dbal)
    {
    }

    public function insert(
        string $table,
        string $level,
        string $message,
        string $datetime,
        ?string $context,
        ?string $extra,
    ): void {
        try {
            if (!$this->dbal->isConnected()) {
                $this->dbal->connect();
            }
            $statement = $this->getStatement($table);
            $statement->executeStatement(compact('level', 'message', 'datetime', 'context', 'extra'));
        } catch (Throwable $exception) {
            throw new ConnectionException('Failed to insert record', previous: $exception);
        }
    }

    public function close(): void
    {
        $this->dbal->close();
    }

    /**
     * @param string $table
     * @return Statement
     * @throws ConnectionException
     */
    private function getStatement(string $table): Statement
    {
        if (!isset($this->statements[$table])) {
            try {
                $this->statements[$table] = $this->dbal->prepare(
                    sprintf(
                        'INSERT INTO %s (level, message, datetime, context, extra) '
                        . 'VALUES (:level, :message, :datetime, :context, :extra)',
                        $table,
                    ),
                );
            } catch (Throwable $exception) {
                throw new ConnectionException('Failed to create statement', previous: $exception);
            }
        }

        return $this->statements[$table];
    }
}
