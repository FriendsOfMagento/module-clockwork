<?php

declare(strict_types=1);

namespace Fom\Clockwork\Service;

use Clockwork\Clockwork;
use Fom\Clockwork\Model\Handler;
use Fom\Clockwork\Model\Config;
use Fom\Clockwork\Profiler\Driver as ProfilerDriver;
use Magento\Developer\Helper\Data as DeveloperHelper;
use Magento\Framework\App\State as AppState;

class Profiler
{
    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DeveloperHelper
     */
    private $developerHelper;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var IncomingRequestResolver
     */
    private $incomingRequestResolver;

    /**
     * @var bool
     */
    private $canCollectFlag;

    /**
     * @param Handler $handler
     * @param Config $config
     * @param DeveloperHelper $developerHelper
     * @param AppState $state
     * @param IncomingRequestResolver $incomingRequestResolver
     */
    public function __construct(
        Handler $handler,
        Config $config,
        DeveloperHelper $developerHelper,
        AppState $state,
        IncomingRequestResolver $incomingRequestResolver
    ) {
        $this->handler = $handler;
        $this->config = $config;
        $this->developerHelper = $developerHelper;
        $this->appState = $state;
        $this->incomingRequestResolver = $incomingRequestResolver;
    }

    /**
     * @return bool
     */
    public function canSendHeaders(): bool
    {
        return $this->canCollect() && $this->developerHelper->isDevAllowed();
    }

    /**
     * @return bool
     */
    public function canCollect(): bool
    {
        if ($this->canCollectFlag === null) {
            $this->canCollectFlag = $this->initCanCollectFlag();
        }

        return $this->canCollectFlag;
    }

    /**
     * @return bool
     */
    private function initCanCollectFlag(): bool
    {
        if (!ProfilerDriver::isInitialized()) {
            return false;
        }

        if (!$this->isDeveloperMode() && !$this->config->canCollectAlways()) {
            return false;
        }

        $clockwork = $this->getClockwork();
        $shouldCollect = $clockwork->shouldCollect()->filter($this->incomingRequestResolver->resolve());
        $shouldRecord = $clockwork->shouldRecord()->filter($clockwork->request());

        return $shouldCollect && $shouldRecord;
    }

    /**
     * @return bool
     */
    private function isDeveloperMode(): bool
    {
        return $this->appState->getMode() === AppState::MODE_DEVELOPER;
    }

    /**
     * @return Clockwork
     */
    private function getClockwork(): Clockwork
    {
        return $this->handler->get();
    }
}
