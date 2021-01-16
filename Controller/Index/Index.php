<?php

declare(strict_types=1);

namespace Fom\Clockwork\Controller\Index;

use Fom\Clockwork\Service\Profiler;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var Profiler
     */
    private $profiler;

    /**
     * @param Context $context
     * @param Profiler $profiler
     */
    public function __construct(
        Context $context,
        Profiler $profiler
    ) {
        $this->profiler = $profiler;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        if ($this->profiler->canSendHeaders()) {
            /** @var \Magento\Framework\View\Result\Page $result */
            $result = $this->resultFactory->create(
                ResultFactory::TYPE_PAGE,
                ['template' => 'Fom_Clockwork::root.phtml']
            );
            $layoutUpdate = $result->getLayout()->getUpdate();
            $layoutUpdate->removeHandle('default');
            $layoutUpdate->addHandle('fom_clockwork_index_index');
        } else {
            /** @var \Magento\Framework\Controller\Result\Forward $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD)
                ->forward('noRoute');
        }

        return $result;
    }
}
