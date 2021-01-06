<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Configurator;

use Clockwork\Clockwork;
use Fom\Clockwork\Model\Config;

class Recorder implements ConfiguratorInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function configure(Clockwork $clockwork): void
    {
        $clockwork->shouldRecord(
            [
                'errorsOnly' => $this->config->isErrorsOnly(),
                'slowOnly'   => $this->config->isSlowOnly() ? $this->config->getSlowThreshold() : false
            ]
        );
    }
}
