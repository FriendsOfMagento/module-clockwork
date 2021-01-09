<?php

declare(strict_types=1);

namespace Fom\Clockwork\App\Response\HeaderProvider;

use Fom\Clockwork\Service\Profiler;

trait HeaderProviderTrait
{
    /**
     * @var Profiler
     */
    private $profiler;

    /**
     * @param Profiler $profiler
     */
    public function __construct(Profiler $profiler)
    {
        $this->profiler = $profiler;
    }

    /**
     * @return bool
     */
    public function canApply(): bool
    {
        return $this->profiler->canSendHeaders();
    }
}
