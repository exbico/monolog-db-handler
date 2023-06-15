<?php

declare(strict_types=1);

namespace Exbico\Handler;

use Monolog\Logger;

/**
 * @phpstan-import-type LevelName from Logger
 */
final class DbHandlerConfig
{
    public function __construct(
        public ?string $emergency = 'log_emergency',
        public ?string $alert = 'log_alert',
        public ?string $critical = 'log_critical',
        public ?string $error = 'log_error',
        public ?string $warning = 'log_warning',
        public ?string $notice = 'log_notice',
        public ?string $info = 'log_info',
        public ?string $debug = 'log_debug',
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
     * @param string $level
     * @phpstan-param LevelName $level
     * @return string
     */
    public function getTable(string $level): string
    {
        return $this->{strtolower($level)};
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
