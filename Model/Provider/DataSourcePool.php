<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Provider;

use Clockwork\DataSource\DataSourceInterface;
use Magento\Framework\Exception\LocalizedException;

class DataSourcePool
{
    private const KEY_INSTANCE = 'data_source';
    private const KEY_SORT_ORDER = 'order';

    private const DEFAULT_ORDER = 10000;

    /**
     * @var array
     */
    private $dataSourceList;

    /**
     * @param array $dataSourceList
     *
     * @throws LocalizedException
     */
    public function __construct(array $dataSourceList = [])
    {
        $this->dataSourceList = $this->prepare($dataSourceList);
    }

    /**
     * @return DataSourceInterface[]
     */
    public function get(): array
    {
        return $this->dataSourceList;
    }

    /**
     * @param array $dataSourceList
     *
     * @return DataSourceInterface[]
     * @throws LocalizedException
     */
    private function prepare(array $dataSourceList): array
    {
        $preparedDataSourceList = [];

        usort($dataSourceList, [$this, 'compare']);
        foreach ($dataSourceList as $dataSourceKey => $dataSourceItem) {
            $instance = $dataSourceItem[self::KEY_INSTANCE] ?? null;
            if (!$instance instanceof DataSourceInterface) {
                throw new LocalizedException(
                    __(
                        "The data source with the key '%1' does not implement the interface '%2'.",
                        $dataSourceKey,
                        DataSourceInterface::class
                    )
                );
            }

            $preparedDataSourceList[] = $instance;
        }

        return $preparedDataSourceList;
    }

    /**
     * @param array $aDataSource
     * @param array $bDataSource
     *
     * @return int
     */
    private function compare(array $aDataSource, array $bDataSource): int
    {
        $aOrder = (int)($aDataSource[self::KEY_SORT_ORDER] ?? self::DEFAULT_ORDER);
        $bOrder = (int)($bDataSource[self::KEY_SORT_ORDER] ?? self::DEFAULT_ORDER);

        return $aOrder <=> $bOrder;
    }
}
