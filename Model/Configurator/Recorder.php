<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Configurator;

use Clockwork\Clockwork;
use Fom\Clockwork\Model\Provider\Recorder as Provider;

class Recorder implements ConfiguratorInterface
{
    /**
     * @var Provider
     */
    private $provider;

    /**
     * @param Provider $provider
     */
    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param Clockwork $clockwork
     *
     * @return void
     */
    public function configure(Clockwork $clockwork): void
    {
        $clockwork->shouldRecord($this->provider->get());
    }
}
