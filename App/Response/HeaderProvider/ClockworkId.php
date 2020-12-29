<?php

declare(strict_types=1);

namespace Fom\Clockwork\App\Response\HeaderProvider;

use Fom\Clockwork\Service\RequestIdResolver;
use Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface;

class ClockworkId implements HeaderProviderInterface
{
    private const HEADER_NAME = 'X-Clockwork-Id';

    /**
     * @var RequestIdResolver
     */
    private $requestIdResolver;

    /**
     * @param RequestIdResolver $requestIdResolver
     */
    public function __construct(RequestIdResolver $requestIdResolver)
    {
        $this->requestIdResolver = $requestIdResolver;
    }

    /**
     * @return bool
     */
    public function canApply(): bool
    {
        return true; // TODO: check clockwork enabled
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
