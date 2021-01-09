<?php

declare(strict_types=1);

namespace Fom\Clockwork\Profiler;

use Fom\Clockwork\Api\Data\EventInterface;
use Magento\Framework\Profiler\DriverInterface;

/**
 * @phpcs:disable Magento2.Functions.StaticFunction.StaticFunction
 */
class Driver implements DriverInterface
{
    private const TIMER_ID_PATTERN = '{timer} ({index})';

    private const PLACEHOLDER_TIMER = '{timer}';
    private const PLACEHOLDER_INDEX = '{index}';

    /**
     * @var bool
     */
    private static $isInitialized = false;

    /**
     * @var array
     */
    private static $timers = [];

    /**
     * @var array
     */
    private static $startedTimersCounter = [];
    private static $stoppedTimersCounter = [];

    /**
     * @var array|null
     */
    private $config;

    /**
     * @param array|null $config
     */
    public function __construct(array $config = null)
    {
        self::$isInitialized = true;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public static function isInitialized(): bool
    {
        return self::$isInitialized;
    }

    /**
     * @return array
     */
    public static function getTimers(): array
    {
        return self::$timers;
    }

    /**
     * @param string $timerId
     * @param array|null $tags
     *
     * @return void
     */
    public function start($timerId, array $tags = null): void
    {
        $timerId = $this->getStartTimerId((string)$timerId);

        // TODO: throw exception or log when event already exists
        if (!array_key_exists($timerId, self::$timers)) {
            self::$timers[$timerId] = [
                EventInterface::START => microtime(true),
                EventInterface::END => null,
            ];
        }
    }

    public function stop($timerId): void
    {
        $timerId = $this->getStopTimerId((string)$timerId);

        // TODO: throw exception or log when event does not exist
        if (array_key_exists($timerId, self::$timers)) {
            self::$timers[$timerId][EventInterface::END] = microtime(true);
        }
    }

    public function clear($timerId = null): void
    {
        if ($timerId === null) {
            self::$timers = [];
        }
    }

    /**
     * @param string $timerId
     *
     * @return string
     */
    private function getStartTimerId(string $timerId): string
    {
        if (array_key_exists($timerId, self::$startedTimersCounter)) {
            self::$startedTimersCounter[$timerId]++;
        } else {
            self::$startedTimersCounter[$timerId] = 0;
        }

        return $this->getTimerId($timerId, self::$startedTimersCounter[$timerId]);
    }

    /**
     * @param string $timerId
     *
     * @return string
     */
    private function getStopTimerId(string $timerId): string
    {
        if (array_key_exists($timerId, self::$stoppedTimersCounter)) {
            self::$stoppedTimersCounter[$timerId]++;
        } else {
            self::$stoppedTimersCounter[$timerId] = 0;
        }

        return $this->getTimerId($timerId, self::$stoppedTimersCounter[$timerId]);
    }

    /**
     * @param string $timerId
     * @param int $index
     *
     * @return string
     */
    private function getTimerId(string $timerId, int $index): string
    {
        if ($index) {
            $timerId = str_replace(
                [self::PLACEHOLDER_TIMER, self::PLACEHOLDER_INDEX],
                [$timerId, $index],
                self::TIMER_ID_PATTERN
            );
        }

        return $timerId;
    }
}
