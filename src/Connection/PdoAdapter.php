<?php

declare(strict_types=1);

namespace Exbico\Handler\Connection;

use PDO;
use PDOStatement;
use Throwable;

final class PdoAdapter implements Connection
{
    /** @var array<string, PDOStatement> $statements */
    private array $statements = [];

    public function __construct(private PDO $pdo)
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
        $statement = $this->getStatement($table);

        try {
            $statement->execute(compact('level', 'message', 'datetime', 'context', 'extra'));
        } catch (Throwable $exception) {
            throw new ConnectionException('Failed to insert record', previous: $exception);
        }
    }

    public function close(): void
    {
    }

    /**
     * @param string $table
     * @return PDOStatement
     * @throws ConnectionException
     */
    private function getStatement(string $table): PDOStatement
    {
        if (!isset($this->statements[$table])) {
            try {
                $this->statements[$table] = $this->pdo->prepare(
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
