<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Configurator;

use Clockwork\Clockwork;
use Fom\Clockwork\Model\Provider\DataSourcePool;

class DataSource implements ConfiguratorInterface
{
    /**
     * @var DataSourcePool
     */
    private $dataSourcePool;

    /**
     * @param DataSourcePool $dataSourcePool
     */
    public function __construct(DataSourcePool $dataSourcePool)
    {
        $this->dataSourcePool = $dataSourcePool;
    }

    /**
     * @param Clockwork $clockwork
     *
     * @return void
     */
    public function configure(Clockwork $clockwork): void
    {
        foreach ($this->dataSourcePool->get() as $dataSource) {
            $clockwork->addDataSource($dataSource);
        }
    }
}
