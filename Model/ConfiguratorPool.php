<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model;

use Fom\Clockwork\Model\Configurator\ConfiguratorInterface;
use Magento\Framework\Exception\LocalizedException;

class ConfiguratorPool
{
    private const KEY_INSTANCE = 'configurator';
    private const KEY_SORT_ORDER = 'order';

    private const DEFAULT_ORDER = 10000;

    /**
     * @var array
     */
    private $configuratorList;

    /**
     * @param array $configuratorList
     *
     * @throws LocalizedException
     */
    public function __construct(array $configuratorList = [])
    {
        $this->configuratorList = $this->prepare($configuratorList);
    }

    /**
     * @return ConfiguratorInterface[]
     */
    public function get(): array
    {
        return $this->configuratorList;
    }

    /**
     * @param array $configuratorList
     *
     * @return ConfiguratorInterface[]
     * @throws LocalizedException
     */
    private function prepare(array $configuratorList): array
    {
        $preparedConfiguratorList = [];

        usort($configuratorList, [$this, 'compare']);
        foreach ($configuratorList as $configuratorKey => $configuratorItem) {
            $instance = $configuratorItem[self::KEY_INSTANCE] ?? null;
            if (!$instance instanceof ConfiguratorInterface) {
                throw new LocalizedException(
                    __(
                        "The configurator with the key '%1' does not implement the interface '%2'.",
                        $configuratorKey,
                        ConfiguratorInterface::class
                    )
                );
            }

            $preparedConfiguratorList[] = $instance;
        }

        return $preparedConfiguratorList;
    }

    /**
     * @param array $aConfigurator
     * @param array $bConfigurator
     *
     * @return int
     */
    private function compare(array $aConfigurator, array $bConfigurator): int
    {
        $aOrder = (int)($aConfigurator[self::KEY_SORT_ORDER] ?? self::DEFAULT_ORDER);
        $bOrder = (int)($bConfigurator[self::KEY_SORT_ORDER] ?? self::DEFAULT_ORDER);

        return $aOrder <=> $bOrder;
    }
}
