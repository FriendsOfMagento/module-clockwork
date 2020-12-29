<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Configurator;

use Clockwork\Clockwork;
use Magento\Framework\Exception\LocalizedException;

interface ConfiguratorInterface
{
    /**
     * @param Clockwork $clockwork
     *
     * @return void
     * @throws LocalizedException
     */
    public function configure(Clockwork $clockwork): void;
}
