<?php

declare(strict_types=1);

namespace Fom\Clockwork\Service;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Throwable;

class DatabaseNameResolver
{
    /**
     * Config path separator value.
     */
    private const CONFIG_PATH_SEPARATOR = '/';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @return string
     */
    public function resolve(): string
    {
        if ($this->databaseName === null) {
            try {
                $this->databaseName = $this->deploymentConfig->get($this->getDatabaseNameConfigPath());
            } catch (Throwable $e) {
                $this->databaseName = '';
            }
        }

        return $this->databaseName;
    }

    /**
     * @return string
     */
    private function getDatabaseNameConfigPath(): string
    {
        return implode(
            self::CONFIG_PATH_SEPARATOR,
            [
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT,
                ConfigOptionsListConstants::KEY_NAME
            ]
        );
    }
}
