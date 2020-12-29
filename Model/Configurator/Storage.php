<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Configurator;

use Clockwork\Clockwork;
use Fom\Clockwork\Model\Provider\Storage as StorageProvider;
use Magento\Framework\Exception\FileSystemException;

class Storage implements ConfiguratorInterface
{
    /**
     * @var StorageProvider
     */
    private $storageProvider;

    /**
     * @param StorageProvider $storageProvider
     */
    public function __construct(StorageProvider $storageProvider)
    {
        $this->storageProvider = $storageProvider;
    }

    /**
     * @param Clockwork $clockwork
     *
     * @return void
     * @throws FileSystemException
     */
    public function configure(Clockwork $clockwork): void
    {
        $clockwork->setStorage($this->storageProvider->get());
    }
}
