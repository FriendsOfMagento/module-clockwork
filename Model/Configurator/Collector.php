<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Configurator;

use Clockwork\Clockwork;
use Clockwork\Request\ShouldCollect;
use Fom\Clockwork\Controller\Router;
use Fom\Clockwork\Model\Config;

class Collector implements ConfiguratorInterface
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
     * @param Clockwork $clockwork
     *
     * @return void
     */
    public function configure(Clockwork $clockwork): void
    {
        $collector = $this->getCollector($clockwork);
        if ($collector) {
            $collector->except(Router::CLOCKWORK_PATH);
        }
    }

    /**
     * @param Clockwork $clockwork
     *
     * @return ShouldCollect|null
     * @todo validate return object
     */
    private function getCollector(Clockwork $clockwork): ?ShouldCollect
    {
        return $clockwork->shouldCollect(
            [
                'onDemand'        => $this->config->isOnDemand(),
                'sample'          => $this->config->getSampleCount(),
                'except'          => $this->config->getExceptUriList(),
                'only'            => $this->config->getOnlyUriList(),
                'exceptPreflight' => $this->config->isExceptPreflight(),
            ]
        );
    }
}
