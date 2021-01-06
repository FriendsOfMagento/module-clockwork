<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model;

use Clockwork\Clockwork;
use Clockwork\ClockworkFactory;
use Magento\Framework\Exception\LocalizedException;

class Handler
{
    /**
     * @var ClockworkFactory
     */
    private $clockworkFactory;

    /**
     * @var ConfiguratorPool
     */
    private $configuratorPool;

    /**
     * @var Clockwork
     */
    private $clockwork;

    /**
     * @param ClockworkFactory $clockworkFactory
     * @param ConfiguratorPool $configuratorPool
     */
    public function __construct(ClockworkFactory $clockworkFactory, ConfiguratorPool $configuratorPool)
    {
        $this->clockworkFactory = $clockworkFactory;
        $this->configuratorPool = $configuratorPool;
    }

    /**
     * @return Clockwork
     */
    public function get(): Clockwork
    {
        if ($this->clockwork === null) {
            $this->clockwork = $this->initialize();
        }

        return $this->clockwork;
    }

    /**
     * @return Clockwork
     */
    private function initialize(): Clockwork
    {
        $clockwork = $this->clockworkFactory->create();

        foreach ($this->configuratorPool->get() as $configurator) {
            try {
                $configurator->configure($clockwork);
            } catch (LocalizedException $e) {
                // TODO: May be add log?
                continue;
            }
        }

        return $clockwork;
    }
}
