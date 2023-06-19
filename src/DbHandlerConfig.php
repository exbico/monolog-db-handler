<?php

declare(strict_types=1);

namespace Exbico\Handler;

use Monolog\Level;

final class DbHandlerConfig
{
    public function __construct(
        public readonly ?string $emergency = 'log_emergency',
        public readonly ?string $alert = 'log_alert',
        public readonly ?string $critical = 'log_critical',
        public readonly ?string $error = 'log_error',
        public readonly ?string $warning = 'log_warning',
        public readonly ?string $notice = 'log_notice',
        public readonly ?string $info = 'log_info',
        public readonly ?string $debug = 'log_debug',
    ) {
    }

    /**
     * @return list<string>
     */
    public function getTables(): array
    {
        $tables = [];

        foreach (get_object_vars($this) as $table) {
            if (is_string($table)) {
                $tables[] = $table;
            }
        }

        return array_unique($tables);
    }

    /**
     * @param Level $level
     * @return string
     */
    public function getTable(Level $level): string
    {
        return $this->{strtolower($level->getName())};
    }

    /**
     * @return list<string>
     */
    public function getLevels(): array
    {
        $levels = [];

        foreach (get_object_vars($this) as $level => $table) {
            if ($table !== null) {
                $levels[] = strtoupper($level);
            }
        }

        return $levels;
    }
}
