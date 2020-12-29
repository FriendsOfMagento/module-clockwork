<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Provider\DataSource;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Fom\Clockwork\Api\Data\EventInterface;
use Fom\Clockwork\Profiler\Driver;
use Magento\Framework\Profiler as BaseProfiler;

class Profiler extends DataSource
{
    /**
     * @param Request $request
     *
     * @return void
     */
    public function resolve(Request $request): void
    {
        foreach (Driver::getTimers() as $timerName => $timerData) {
            $request->timeline()
                ->event($this->getTimerName($timerName))
                ->start($this->getStartTime($timerData))
                ->end($this->getEndTime($timerData))
                ->duration($this->getDuration($timerData));
        }
    }

    /**
     * @param string $timerName
     *
     * @return string
     */
    private function getTimerName(string $timerName): string
    {
        $timerNameParts = explode(BaseProfiler::NESTING_SEPARATOR, trim($timerName));

        return (string)end($timerNameParts);
    }

    /**
     * @param array $timerData
     *
     * @return float|null
     */
    private function getStartTime(array $timerData): ?float
    {
        return $timerData[EventInterface::START] ?? null;
    }

    /**
     * @param array $timerData
     *
     * @return float|null
     */
    private function getEndTime(array $timerData): ?float
    {
        return $timerData[EventInterface::END] ?? null;
    }

    /**
     * @param array $timerData
     *
     * @return float
     */
    private function getDuration(array $timerData): float
    {
        $startTime = (float)$this->getStartTime($timerData);
        $endTime = (float)$this->getEndTime($timerData);

        return ($endTime - $startTime) * 1000;
    }
}
