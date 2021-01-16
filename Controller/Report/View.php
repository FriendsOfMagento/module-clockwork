<?php

declare(strict_types=1);

namespace Fom\Clockwork\Controller\Report;

use Clockwork\Request\Request;
use Clockwork\Storage\FileStorage;
use Clockwork\Storage\Search;
use Fom\Clockwork\Model\Handler;
use Fom\Clockwork\Model\Provider\Storage as StorageProvider;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Controller\ResultInterface;
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
     * @var Handler
     */
    private $handler;

    /**
     * @param Context $context
     * @param StorageProvider $storageProvider
     * @param JsonResultFactory $jsonResultFactory
     * @param Handler $handler
     */
    public function __construct(
        Context $context,
        StorageProvider $storageProvider,
        JsonResultFactory $jsonResultFactory,
        Handler $handler
    ) {
        parent::__construct($context);
        $this->storageProvider = $storageProvider;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->handler = $handler;
    }

    /**
     * @return JsonResult|ResultInterface
     */
    public function execute(): ResultInterface
    {
        $request = $this->getRequest();
        $id = (string)$this->getRequest()->getParam('id');
        if ($id === 'latest') {
            $data = $this->getLatestData();
        } else {
            $data = $this->getDataById(
                $id,
                $request->getParam('direction'),
                $request->getParam('count'),
                $request->getParam('extended')
            );
        }

        return $this->jsonResultFactory->create()->setData($data);
    }

    /**
     * @return array
     */
    private function getLatestData(): array
    {
        try {
            $data = [];
            $reports = $this->getStorage()->latest(Search::fromRequest($this->getRequest()->getParams()));
            foreach ($reports as $report) {
                if ($report instanceof Request) {
                    $data[] = $report->toArray();
                }
            }
        } catch (FileSystemException $e) {
            $data = [];
        }

        return $data;
    }

    /**
     * @param string $id
     * @param string|null $direction
     * @param int|null $count
     * @param bool $extended
     *
     * @return array
     */
    private function getDataById(string $id, string $direction = null, int $count = null, bool $extended = false): array
    {
        try {
            $data = [];
            $storage = $this->getStorage();
            $search = Search::fromRequest($this->getRequest()->getParams());
            switch ($direction) {
                case 'previous':
                    $report = $storage->previous($id, $count, $search);
                    break;
                case 'next':
                    $report = $storage->next($id, $count, $search);
                    break;
                default:
                    $report = $storage->find($id);
            }

            if ($extended) {
                $clockwork = $this->handler->get();
                $clockwork->extendRequest($report);
            }

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
