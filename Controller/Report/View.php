<?php

declare(strict_types=1);

namespace Fom\Clockwork\Controller\Report;

use Clockwork\Request\Request;
use Clockwork\Storage\FileStorage;
use Fom\Clockwork\Model\Provider\Storage as StorageProvider;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Exception\FileSystemException;

class View extends Action implements HttpGetActionInterface
{
    /**
     * @var JsonResultFactory
     */
    private $jsonResultFactory;

    /**
     * @var StorageProvider
     */
    private $storageProvider;

    /**
     * @param Context $context
     * @param StorageProvider $storageProvider
     * @param JsonResultFactory $jsonResultFactory
     */
    public function __construct(
        Context $context,
        StorageProvider $storageProvider,
        JsonResultFactory $jsonResultFactory
    ) {
        parent::__construct($context);
        $this->storageProvider = $storageProvider;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    /**
     * @return JsonResult
     */
    public function execute(): JsonResult
    {
        $data = [];

        if ($id = (string)$this->getRequest()->getParam('id')) {
            $data = $this->getReportData($id);
        }

        return $this->jsonResultFactory->create()->setData($data);
    }

    /**
     * @param string $id
     *
     * @return array
     */
    private function getReportData(string $id): array
    {
        try {
            $data = [];

            $report = $this->getStorage()->find($id);
            if ($report instanceof Request) {
                $data = $report->toArray();
            }
        } catch (FileSystemException $e) {
            $data = [];
        }

        return $data;
    }

    /**
     * @return FileStorage
     * @throws FileSystemException
     */
    private function getStorage(): FileStorage
    {
        return $this->storageProvider->get();
    }
}
