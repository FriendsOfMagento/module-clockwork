<?php

declare(strict_types=1);

namespace Fom\Clockwork\Service;

use Clockwork\Clockwork;
use Clockwork\Request\Request;
use Fom\Clockwork\Model\Handler;

class RequestIdResolver
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
     * @return string
     */
    public function resolve(): string
    {
        return $this->getRequest()->id;
    }

    /**
     * @return Request
     */
    private function getRequest(): Request
    {
        return $this->getClockwork()->getRequest();
    }

    /**
     * @return Clockwork
     */
    private function getClockwork(): Clockwork
    {
        return $this->handler->get();
    }
}
