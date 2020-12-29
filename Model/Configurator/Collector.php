<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Configurator;

use Clockwork\Clockwork;
use Clockwork\Request\ShouldCollect;
use Fom\Clockwork\Controller\Router;

// TODO: requests config provider (on_demand, errors_only, slow_threshold and etc.)
class Collector implements ConfiguratorInterface
{
    /**
     * @param Clockwork $clockwork
     *
     * @return void
     */
    public function configure(Clockwork $clockwork): void
    {
        $collector = $this->getCollector($clockwork);
        $collector->except(Router::CLOCKWORK_PATH);
    }

    /**
     * @param Clockwork $clockwork
     *
     * @return ShouldCollect
     */
    private function getCollector(Clockwork $clockwork): ShouldCollect
    {
        return $clockwork->shouldCollect();
    }
}
