<?php

declare(strict_types=1);

namespace Fom\Clockwork\Db;

use Exception;
use Fom\Clockwork\Service\DatabaseNameResolver;
use Magento\Framework\DB\LoggerInterface;
use Zend_Db_Statement_Exception;
use Zend_Db_Statement_Pdo;

class Logger implements LoggerInterface
{
    /**
     * @var DatabaseNameResolver
     */
    private $databaseNameResolver;

    /**
     * @var bool
     */
    private $logAllQueries;

    /**
     * @var float
     */
    private $logQueryTime;

    /**
     * @var bool
     */
    private $logCallStack;

    /**
     * @var float
     */
    private $timer;

    /**
     * @var array
     */
    private static $queries = [];

    /**
     * @param DatabaseNameResolver $databaseNameResolver
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     */
    public function __construct(
        DatabaseNameResolver $databaseNameResolver,
        bool $logAllQueries = false,
        float $logQueryTime = 0.05,
        bool $logCallStack = false
    ) {
        $this->databaseNameResolver = $databaseNameResolver;
        $this->logAllQueries = $logAllQueries;
        $this->logQueryTime = $logQueryTime;
        $this->logCallStack = $logCallStack;
    }

    /**
     * @return array
     */
    public static function getQueries(): array
    {
        return self::$queries;
    }

    /**
     * @return void
     */
    public function startTimer(): void
    {
        $this->timer = microtime(true);
    }

    /**
     * @param string $type
     * @param string $sql
     * @param array $bind
     * @param Zend_Db_Statement_Pdo|null $result
     *
     * @return void
     * @throws Zend_Db_Statement_Exception
     */
    public function logStats($type, $sql, $bind = [], $result = null): void
    {
        $stats = $this->createQuery($type, $sql, $bind);
        if (!empty($stats)) {
            $this->log($stats);
        }
    }

    /**
     * @param Exception $e
     *
     * @return void
     */
    public function critical(Exception $e): void
    {
        $this->log(['query' => $e->getMessage()]);
    }

    /**
     * @param array $query
     *
     * @return void
     */
    public function log($query): void
    {
        self::$queries[] = $query;
    }

    /**
     * Get formatted statistics message
     *
     * @param string $type Type of query
     * @param string $sql
     * @param array $bind
     *
     * @return array
     */
    private function createQuery($type, $sql, $bind = []): array
    {
        if ($type !== self::TYPE_QUERY) {
            return [];
        }

        $duration = microtime(true) - $this->timer;
        if (!$this->logAllQueries && $duration < $this->logQueryTime) {
            return [];
        }

        return [
            'query' => $sql,
            'bindings' => $bind,
            'time' => $this->timer,
            'connection' => $this->databaseNameResolver->resolve(),
            'duration' => $duration * 1000,
        ];
    }
}
