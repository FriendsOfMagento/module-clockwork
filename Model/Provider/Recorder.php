<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Provider;

use Fom\Clockwork\Model\Config;

class Recorder
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

    /**
     * @return array
     */
    public function get(): array
    {
        return [
            'errorsOnly' => $this->config->isErrorsOnly(),
            'slowOnly' => $this->config->isSlowOnly() ? $this->config->getSlowThreshold() : false
        ];
    }
}
