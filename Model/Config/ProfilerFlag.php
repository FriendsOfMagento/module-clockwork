<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Config;

use Fom\Clockwork\Profiler\Driver;
use InvalidArgumentException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList as Filesystem;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Setup\JsonPersistor;

class ProfilerFlag
{
    /**
     * Profiler flag file
     */
    private const PROFILER_FLAG_FILE = 'profiler.flag';

    /**
     * Key for drivers configuration value.
     */
    private const KEY_DRIVERS = 'drivers';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * @var JsonPersistor
     */
    private $jsonPersistor;

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @param Filesystem $filesystem
     * @param FileDriver $fileDriver
     * @param JsonPersistor $jsonPersistor
     * @param JsonSerializer $jsonSerializer
     */
    public function __construct(
        Filesystem $filesystem,
        FileDriver $fileDriver,
        JsonPersistor $jsonPersistor,
        JsonSerializer $jsonSerializer
    ) {
        $this->jsonPersistor = $jsonPersistor;
        $this->fileDriver = $fileDriver;
        $this->filesystem = $filesystem;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @return bool
     */
    public function isNeedToOverride(): bool
    {
        try {
            $data = $this->fileDriver->fileGetContents($this->getProfilerFlagPath());
        } catch (FileSystemException $e) {
            $data = '';
        }

        if ($data === '') {
            return false;
        }

        try {
            $unserializedData = $this->jsonSerializer->unserialize($data);
        } catch (InvalidArgumentException $e) {
            $unserializedData = [];
        }

        if (is_array($unserializedData)
            && !empty($unserializedData)
            && array_key_exists(self::KEY_DRIVERS, $unserializedData)
        ) {
            return $unserializedData[self::KEY_DRIVERS] !== Driver::class;
        }

        return true;
    }

    /**
     * @return void
     * @throws FileSystemException
     */
    public function enable(): void
    {
        $this->jsonPersistor->persist(
            [self::KEY_DRIVERS => Driver::class],
            $this->getProfilerFlagPath()
        );
    }

    /**
     * @return void
     * @throws FileSystemException
     */
    public function disable(): void
    {
        $this->fileDriver->deleteFile($this->getProfilerFlagPath());
    }

    /**
     * @return string
     * @throws FileSystemException
     */
    private function getProfilerFlagPath(): string
    {
        return $this->filesystem->getPath(DirectoryList::VAR_DIR)
            . DIRECTORY_SEPARATOR
            . self::PROFILER_FLAG_FILE;
    }
}
