<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Provider;

use Fom\Clockwork\Model\Config;

class Collector
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
            'onDemand' => $this->config->isOnDemand(),
            'sample' => $this->config->getSampleCount(),
            'except' => $this->config->getExceptUriList(),
            'only' => $this->config->getOnlyUriList(),
            'exceptPreflight' => $this->config->isExceptPreflight(),
        ];
    }
}
