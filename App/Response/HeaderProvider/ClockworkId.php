<?php

declare(strict_types=1);

namespace Fom\Clockwork\App\Response\HeaderProvider;

use Fom\Clockwork\Service\Profiler;
use Fom\Clockwork\Service\RequestIdResolver;
use Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface;

class ClockworkId implements HeaderProviderInterface
{
    private const HEADER_NAME = 'X-Clockwork-Id';

    use HeaderProviderTrait;

    /**
     * @var RequestIdResolver
     */
    private $requestIdResolver;

    /**
     * @param Profiler $profiler
     * @param RequestIdResolver $requestIdResolver
     */
    public function __construct(
        Profiler $profiler,
        RequestIdResolver $requestIdResolver
    ) {
        $this->profiler = $profiler;
        $this->requestIdResolver = $requestIdResolver;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::HEADER_NAME;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->requestIdResolver->resolve();
    }
}
