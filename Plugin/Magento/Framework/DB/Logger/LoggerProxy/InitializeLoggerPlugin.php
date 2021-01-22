<?php

declare(strict_types=1);

namespace Fom\Clockwork\Plugin\Magento\Framework\DB\Logger\LoggerProxy;

use Fom\Clockwork\Db\LoggerInitializer;
use Magento\Framework\DB\Logger\LoggerProxy;

class InitializeLoggerPlugin
{
    /**
     * @var LoggerInitializer
     */
    private $initializer;

    /**
     * @param LoggerInitializer $initializer
     */
    public function __construct(LoggerInitializer $initializer)
    {
        $this->initializer = $initializer;
    }

    /**
     * @param LoggerProxy $loggerProxy
     *
     * @return void
     */
    public function beforeLog(LoggerProxy $loggerProxy): void
    {
        $this->initializeLogger($loggerProxy);
    }

    /**
     * @param LoggerProxy $loggerProxy
     *
     * @return void
     */
    public function beforeLogStats(LoggerProxy $loggerProxy): void
    {
        $this->initializeLogger($loggerProxy);
    }

    /**
     * @param LoggerProxy $loggerProxy
     *
     * @return void
     */
    public function beforeCritical(LoggerProxy $loggerProxy): void
    {
        $this->initializeLogger($loggerProxy);
    }

    /**
     * @param LoggerProxy $loggerProxy
     *
     * @return void
     */
    public function beforeStartTimer(LoggerProxy $loggerProxy): void
    {
        $this->initializeLogger($loggerProxy);
    }

    /**
     * @param LoggerProxy $loggerProxy
     *
     * @return void
     */
    private function initializeLogger(LoggerProxy $loggerProxy): void
    {
        $this->initializer->initialize($loggerProxy);
    }
}
