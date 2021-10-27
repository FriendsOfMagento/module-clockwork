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
    private const APP_JS_FILENAME = 'app.js';
    private const APP_CSS_FILENAME = 'app.css';
    private const APP_JS_VENDOR_FILENAME = 'chunk-vendors.js';

    private const VENDOR_WEB_PATH_PARTS = [
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
            self::APP_JS_FILENAME,
            self::APP_JS_VENDOR_FILENAME,
        ],
        'css' => [
            self::APP_CSS_FILENAME,
        ],
    ];

    /**
     * @var string[]
     */
    private $regexListByFilename = [
        self::APP_JS_FILENAME        => "/app.\S*\d+\S*.js/",
        self::APP_JS_VENDOR_FILENAME => "/chunk-vendors.\S*\d+\S*.js/",
        self::APP_CSS_FILENAME       => "/css.\S*\d+\S*.css/",
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
     * @param string|null $additionalFolder
     *
     * @return bool
     * @throws FileSystemException
     */
    private function isFileValid(string $localPath, string $filename, string $additionalFolder = null): bool
    {
        $vendorPath = $this->getVendorFilePath($filename, $additionalFolder);
        if ($vendorPath && $this->getFileDriver()->isFile($localPath)) {
            $localHash = hash_file('sha256', $localPath);
            if ($filename === self::APP_JS_FILENAME) {
                $vendorHash = hash('sha256', $this->replaceJsImages($this->replaceVendorImages($vendorPath)));
            } else {
                $vendorHash = hash('sha256', $this->replaceVendorImages($vendorPath));
            }

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

        if ($filename === self::APP_JS_FILENAME) {
            $content = $this->replaceJsImages($this->replaceVendorImages($vendorPath));
        } else {
            $content = $this->replaceVendorImages($vendorPath);
        }

        $this->fileWriteFactory->create($localPath, DriverPool::FILE, 'w+')
            ->write($content);
    }

    /**
     * @param string $content
     *
     * @return string
     * @throws FileSystemException
     */
    private function replaceJsImages(string $content): string
    {
        $content = str_replace(
            '"img/whats-new/"+e.release.version+"/"+',
            '',
            $content
        );

        preg_match_all('/image:\"(\S+\w+)\",image/', $content, $matches);
        list(, $jsImages) = $matches;
        $matchedImages = [];
        $vendorImages = $this->getVendorDirectory()->readRecursively('img/whats-new');
        foreach ($jsImages as $image) {
            foreach ($vendorImages as $path) {
                if (strpos($path, $image) !== false) {
                    $matchedImages[$image] = $path;
                }
            }
        }

        foreach ($matchedImages as $imageName => $path) {
            $content = str_replace(
                $imageName,
                $this->encodeImageContent($path),
                $content
            );
        }

        return $content;
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
                join(DIRECTORY_SEPARATOR, self::VENDOR_WEB_PATH_PARTS)
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
