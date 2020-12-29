<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Configurator;

use Clockwork\Clockwork;
use Clockwork\Helpers\Serializer as SerializerHelper;
use Fom\Clockwork\Model\Provider\Serializer as SerializerProvider;

class Serializer implements ConfiguratorInterface
{
    /**
     * @var SerializerProvider
     */
    private $serializerProvider;

    /**
     * @param SerializerProvider $serializerProvider
     */
    public function __construct(SerializerProvider $serializerProvider)
    {
        $this->serializerProvider = $serializerProvider;
    }

    /**
     * @param Clockwork $clockwork
     *
     * @return void
     */
    public function configure(Clockwork $clockwork): void
    {
        SerializerHelper::defaults($this->serializerProvider->get());
    }
}
