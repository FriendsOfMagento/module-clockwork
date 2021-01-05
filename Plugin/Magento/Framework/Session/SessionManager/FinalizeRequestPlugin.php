<?php

declare(strict_types=1);

namespace Fom\Clockwork\Plugin\Magento\Framework\Session\SessionManager;

use Fom\Clockwork\Service\Profiler;
use Fom\Clockwork\Service\RequestFinalizer;
use Magento\Framework\Session\SessionManager;

class FinalizeRequestPlugin
{
    /**
     * @var RequestFinalizer
     */
    private $requestFinalizer;

    /**
     * @var Profiler
     */
    private $profiler;

    /**
     * @param RequestFinalizer $requestFinalizer
     * @param Profiler $profiler
     */
    public function __construct(
        RequestFinalizer $requestFinalizer,
        Profiler $profiler
    ) {
        $this->requestFinalizer = $requestFinalizer;
        $this->profiler = $profiler;
    }

    /**
     * @param SessionManager $subject
     *
     * @return void
     */
    public function afterWriteClose(SessionManager $subject): void
    {
        if ($this->profiler->canCollect()) {
            $this->requestFinalizer->finalize();
        }
    }
}
