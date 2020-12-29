<?php

declare(strict_types=1);

namespace Fom\Clockwork\Service;

use Clockwork\Clockwork;
use Fom\Clockwork\Model\Handler;

class RequestFinalizer
{
    /**
     * @var Handler
     */
    private $handler;

    /**
     * @param Handler $handler
     */
    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @return void
     */
    public function finalize(): void
    {
        $clockwork = $this->getClockwork();
        $clockwork->resolveRequest();
        $clockwork->storeRequest();
    }

    /**
     * @return Clockwork
     */
    private function getClockwork(): Clockwork
    {
        return $this->handler->get();
    }
}
