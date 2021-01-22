<?php

declare(strict_types=1);

namespace Fom\Clockwork\Db;

use Magento\Framework\DB\Logger\LoggerProxy;
use ReflectionProperty;
use ReflectionPropertyFactory;

class LoggerInitializer
{
    /**
     * @see \Magento\Framework\DB\Logger\LoggerProxy::$logger
     */
    private const PROPERTY_LOGGER = 'logger';

    /**
     * @see Logger::$logAllQueries
     */
    private const LOG_ALL_QUERIES = 'logAllQueries';

    /**
     * @see Logger::$logQueryTime
     */
    private const LOG_QUERY_TIME = 'logQueryTime';

    /**
     * @see Logger::$logCallStack
     */
    private const LOG_CALL_STACK = 'logCallStack';

    /**
     * @var ReflectionPropertyFactory
     */
    private $reflectionPropertyFactory;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var string
     */
    private $loggerAlias;

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
     * @var bool
     */
    private $isInitialized = false;

    /**
     * @var ReflectionProperty
     */
    private $logger;

    /**
     * @param LoggerFactory $loggerFactory
     * @param ReflectionPropertyFactory $reflectionPropertyFactory
     * @param string $loggerAlias
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     */
    public function __construct(
        LoggerFactory $loggerFactory,
        ReflectionPropertyFactory $reflectionPropertyFactory,
        string $loggerAlias,
        bool $logAllQueries = true,
        float $logQueryTime = 0.001,
        bool $logCallStack = true
    ) {
        $this->loggerFactory = $loggerFactory;
        $this->reflectionPropertyFactory = $reflectionPropertyFactory;
        $this->loggerAlias = $loggerAlias;
        $this->logAllQueries = $logAllQueries;
        $this->logQueryTime = $logQueryTime;
        $this->logCallStack = $logCallStack;
    }

    /**
     * @param LoggerProxy $loggerProxy
     *
     * @return void
     */
    public function initialize(LoggerProxy $loggerProxy): void
    {
        if ($this->canInitialize()) {
            $this->addLoggerToProxy($loggerProxy);
            $this->markAsInitialized();
        }
    }

    /**
     * @return bool
     */
    private function canInitialize(): bool
    {
        return !$this->isInitialized && $this->loggerAlias === Logger::ALIAS;
    }

    /**
     * @return void
     */
    private function markAsInitialized(): void
    {
        $this->isInitialized = true;
    }

    /**
     * @param LoggerProxy $loggerProxy
     *
     * @return void
     */
    private function addLoggerToProxy(LoggerProxy $loggerProxy): void
    {
        $this->getLoggerProperty()->setValue(
            $loggerProxy,
            $this->loggerFactory->create(
                [
                    self::LOG_ALL_QUERIES => $this->logAllQueries,
                    self::LOG_QUERY_TIME => $this->logQueryTime,
                    self::LOG_CALL_STACK => $this->logCallStack,
                ]
            )
        );
    }

    /**
     * @return ReflectionProperty
     */
    private function getLoggerProperty(): ReflectionProperty
    {
        if ($this->logger === null) {
            $this->logger = $this->reflectionPropertyFactory->create(
                [
                    'class' => LoggerProxy::class,
                    'name' => self::PROPERTY_LOGGER,
                ]
            );
            $this->logger->setAccessible(true);
        }

        return $this->logger;
    }
}
