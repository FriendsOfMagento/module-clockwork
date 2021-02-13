<?php

declare(strict_types=1);

namespace Fom\Clockwork\Setup;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\WriteFactory as FileWriteFactory;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class RecurringData implements InstallDataInterface
{
    private const APP_JS_PATH = 'app.js';
    private const APP_CSS_PATH = 'app.css';
    private const APP_JS_VENDOR_PATH = 'chunk-vendors.js';

    private const VENDOR_WEB_PATH = [
        'vendor',
        'itsgoingd',
        'clockwork',
        'Clockwork',
        'Web',
        'public',
    ];

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var FileWriteFactory
     */
    private $fileWriteFactory;

    /**
     * @var string[]
     */
    private $folderList = [
        'frontend',
        'web',
    ];

    /**
     * @var string[][]
     */
    private $fileList = [
        'js' => [
            self::APP_JS_PATH,
            self::APP_JS_VENDOR_PATH,
        ],
        'css' => [
            self::APP_CSS_PATH,
        ],
    ];

    /**
     * @var string[]
     */
    private $regexListByFilename = [
        self::APP_JS_PATH => "/app.\S*\d+\S*.js/",
        self::APP_JS_VENDOR_PATH => "/chunk-vendors.\S*\d+\S*.js/",
        self::APP_CSS_PATH => "/css.\S*\d+\S*.css/",
    ];

    /**
     * @var ReadInterface|null
     */
    private $vendorDirectory;

    /**
     * @var DriverPool
     */
    private $driverPool;

    /**
     * @param Reader $moduleReader
     * @param Filesystem $filesystem
     * @param FileWriteFactory $fileWriteFactory
     * @param DriverPool $driverPool
     */
    public function __construct(
        Reader $moduleReader,
        Filesystem $filesystem,
        FileWriteFactory $fileWriteFactory,
        DriverPool $driverPool
    ) {
        $this->moduleReader = $moduleReader;
        $this->filesystem = $filesystem;
        $this->fileWriteFactory = $fileWriteFactory;
        $this->driverPool = $driverPool;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws FileSystemException
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        foreach ($this->fileList as $additionalFolder => $filenames) {
            foreach ($filenames as $filename) {
                if (!$this->isFileValid(
                    $this->getPath($filename, $additionalFolder),
                    $filename,
                    $additionalFolder
                )) {
                    $this->setFileContent(
                        $this->getVendorFilePath($filename, $additionalFolder),
                        $this->getPath($filename, $additionalFolder),
                        $filename
                    );
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getVendorImages(): array
    {
        $imageList = [];
        $images = $this->getVendorDirectory()->readRecursively('img');
        foreach ($images as $path) {
            if (!$this->getVendorDirectory()->isDirectory($path)) {
                $imageList[] = $path;
            }
        }

        return $imageList;
    }

    /**
     * @param string $localPath
     * @param string $filename
     * @param string|null $additionalFolders
     *
     * @return bool
     * @throws FileSystemException
     */
    private function isFileValid(string $localPath, string $filename, string $additionalFolders = null): bool
    {
        $vendorPath = $this->getVendorFilePath($filename, $additionalFolders);
        if ($vendorPath && $this->getFileDriver()->isFile($localPath)) {
            $localHash = hash_file('sha256', $localPath);
            $vendorHash = hash('sha256', $this->replaceVendorImages($vendorPath));
            $result = $localHash === $vendorHash;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param string $path
     *
     * @return string
     * @throws FileSystemException
     */
    private function replaceVendorImages(string $path): string
    {
        $content = $this->getVendorDirectory()->readFile($path);
        foreach ($this->getVendorImages() as $path) {
            $content = str_replace(
                $path,
                $this->encodeImageContent($path),
                $content
            );
        }

        return $content;
    }

    /**
     * @param string $imagePath
     *
     * @return string
     * @throws FileSystemException
     */
    private function encodeImageContent(string $imagePath): string
    {
        $content = $this->getVendorDirectory()->readFile($imagePath);
        $imageType = pathinfo(
            $this->getVendorDirectory()->getAbsolutePath($imagePath),
            PATHINFO_EXTENSION
        );

        return "data:image/$imageType;base64," . base64_encode($content);
    }

    /**
     * @param string $vendorPath
     * @param string $localPath
     * @param string $filename
     *
     * @throws FileSystemException
     */
    private function setFileContent(string $vendorPath, string $localPath, string $filename): void
    {
        $directoryPath = str_ireplace($filename, '', $localPath);
        if (!$this->getFileDriver()->isDirectory($directoryPath)) {
            $this->getFileDriver()->createDirectory($directoryPath);
        }

        $this->fileWriteFactory->create(
            $localPath,
            DriverPool::FILE,
            'w+'
        )->write($this->replaceVendorImages($vendorPath));
    }

    /**
     * @return DriverInterface|File
     */
    private function getFileDriver(): DriverInterface
    {
        return $this->driverPool->getDriver(DriverPool::FILE);
    }

    /**
     * @return ReadInterface
     */
    private function getVendorDirectory(): ReadInterface
    {
        if ($this->vendorDirectory === null) {
            $this->vendorDirectory = $this->filesystem->getDirectoryReadByPath(
                join(DIRECTORY_SEPARATOR, self::VENDOR_WEB_PATH)
            );
        }

        return $this->vendorDirectory;
    }

    /**
     * @param string $filename
     * @param string $additionalFolder
     *
     * @return string|null
     */
    private function getVendorFilePath(string $filename, string $additionalFolder): ?string
    {
        $vendorPath = null;
        $files = $this->getVendorDirectory()->read($additionalFolder);
        foreach ($files as $file) {
            preg_match($this->regexListByFilename[$filename], $file, $matches);
            if (!empty($matches)) {
                $vendorPath = $this->getVendorDirectory()->getAbsolutePath($file);
                break;
            }
        }

        return $vendorPath;
    }

    /**
     * @param string $filename
     * @param string $additionalFolder
     *
     * @return string
     */
    private function getPath(string $filename, string $additionalFolder): string
    {
        return join(
            DIRECTORY_SEPARATOR,
            [$this->getModuleViewPath(), $this->buildFilePath($filename, $additionalFolder)]
        );
    }

    /**
     * @return string
     */
    private function getModuleViewPath(): string
    {
        return $this->moduleReader->getModuleDir(Dir::MODULE_VIEW_DIR, 'Fom_Clockwork');
    }

    /**
     * @param string $filename
     * @param string $additionalFolder
     *
     * @return string
     */
    private function buildFilePath(string $filename, string $additionalFolder): string
    {
        return join(DIRECTORY_SEPARATOR, array_merge($this->folderList, [$additionalFolder, $filename]));
    }
}
