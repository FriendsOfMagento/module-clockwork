<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Provider;

use Clockwork\Storage\FileStorage;
use Clockwork\Storage\FileStorageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;

class Storage
{
    /**
     * A subdirectory to store Clockwork reports.
     */
    private const SUBDIRECTORY = 'clockwork';

    /**
     * Default expiration - 1 day
     */
    private const DEFAULT_EXPIRATION = 1;

    /**
     * @var FileStorageFactory
     */
    private $fileStorageFactory;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var int|null
     */
    private $expiration;

    /**
     * @var FileStorage
     */
    private $storage;

    /**
     * @param FileStorageFactory $fileStorageFactory
     * @param DirectoryList $directoryList
     * @param int|null $expiration
     */
    public function __construct(
        FileStorageFactory $fileStorageFactory,
        DirectoryList $directoryList,
        int $expiration = null
    ) {
        $this->fileStorageFactory = $fileStorageFactory;
        $this->directoryList = $directoryList;
        $this->expiration = $expiration;
    }

    /**
     * @return FileStorage
     * @throws FileSystemException
     */
    public function get(): FileStorage
    {
        if ($this->storage === null) {
            $this->storage = $this->fileStorageFactory->create(
                [
                    'path' => $this->getStoragePath(),
                    'expiration' => $this->getExpiration(),
                ]
            );
        }

        return $this->storage;
    }

    /**
     * @return string
     * @throws FileSystemException
     */
    private function getStoragePath(): string
    {
        return $this->directoryList->getPath(DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . self::SUBDIRECTORY;
    }

    /**
     * @return int
     */
    private function getExpiration(): int
    {
        if ($this->expiration !== null) {
            return $this->expiration;
        }

        return self::DEFAULT_EXPIRATION * 24 * 60;
    }
}
