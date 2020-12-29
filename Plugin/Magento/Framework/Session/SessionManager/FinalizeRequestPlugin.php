<?php

declare(strict_types=1);

namespace Fom\Clockwork\Plugin\Magento\Framework\Session\SessionManager;

use Fom\Clockwork\Service\RequestFinalizer;
use Magento\Framework\Session\SessionManager;

class FinalizeRequestPlugin
{
    /**
     * @var RequestFinalizer
     */
    private $requestFinalizer;

    /**
     * @param RequestFinalizer $requestFinalizer
     */
    public function __construct(RequestFinalizer $requestFinalizer)
    {
        $this->requestFinalizer = $requestFinalizer;
    }

    /**
     * @param SessionManager $subject
     *
     * @return void
     */
    public function afterWriteClose(SessionManager $subject): void
    {
        // TODO: check clockwork enabled
        $this->requestFinalizer->finalize();
    }
}
