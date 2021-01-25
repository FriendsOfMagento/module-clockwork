<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Config;

use Fom\Clockwork\Db\Logger;
use Magento\Framework\App\DeploymentConfig\Writer as DeploymentConfig;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\DB\Logger\LoggerProxy;
use Magento\Framework\Exception\FileSystemException;

class DatabaseLogger
{
    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var string
     */
    private $loggerAlias;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param string $loggerAlias
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        string $loggerAlias
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->loggerAlias = $loggerAlias;
    }

    /**
     * @return bool
     */
    public function isNeedToOverride(): bool
    {
        return $this->loggerAlias !== Logger::ALIAS;
    }

    /**
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     *
     * @return void
     * @throws FileSystemException
     */
    public function enable(bool $logAllQueries = true, float $logQueryTime = 0.001, bool $logCallStack = true): void
    {
        $this->deploymentConfig->saveConfig(
            [
                ConfigFilePool::APP_ENV => [
                    LoggerProxy::CONF_GROUP_NAME => [
                        LoggerProxy::PARAM_ALIAS => Logger::ALIAS,
                        LoggerProxy::PARAM_LOG_ALL => (int)$logAllQueries,
                        LoggerProxy::PARAM_QUERY_TIME => number_format($logQueryTime, 3),
                        LoggerProxy::PARAM_CALL_STACK => (int)$logCallStack,
                    ]
                ]
            ]
        );
    }

    /**
     * @return void
     * @throws FileSystemException
     */
    public function disable(): void
    {
        $this->deploymentConfig->saveConfig(
            [
                ConfigFilePool::APP_ENV => [
                    LoggerProxy::CONF_GROUP_NAME => [
                        LoggerProxy::PARAM_ALIAS => LoggerProxy::LOGGER_ALIAS_DISABLED
                    ]
                ]
            ]
        );
    }
}
