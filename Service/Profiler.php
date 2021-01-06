<?php

declare(strict_types=1);

namespace Fom\Clockwork\Service;

use Fom\Clockwork\Model\Config;
use Magento\Developer\Helper\Data as DeveloperHelper;
use Magento\Framework\App\State;

class Profiler
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DeveloperHelper
     */
    private $developerHelper;

    /**
     * @var State
     */
    private $appState;

    /**
     * @param Config $config
     * @param DeveloperHelper $developerHelper
     * @param State $state
     */
    public function __construct(
        Config $config,
        DeveloperHelper $developerHelper,
        State $state
    ) {
        $this->config = $config;
        $this->developerHelper = $developerHelper;
        $this->appState = $state;
    }

    /**
     * @return bool
     */
    public function canCollect(): bool
    {
        $isEnabled = $this->isDeveloperMode() && $this->developerHelper->isDevAllowed();

        return $isEnabled || $this->config->canCollectAlways();
    }

    /**
     * @return bool
     */
    private function isDeveloperMode(): bool
    {
        return $this->appState->getMode() === State::MODE_DEVELOPER;
    }
}
