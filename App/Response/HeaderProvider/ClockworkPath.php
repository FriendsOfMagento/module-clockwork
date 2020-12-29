<?php

declare(strict_types=1);

namespace Fom\Clockwork\App\Response\HeaderProvider;

use Fom\Clockwork\Controller\Router;
use Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface;

class ClockworkPath implements HeaderProviderInterface
{
    private const HEADER_NAME = 'X-Clockwork-Path';

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
        return Router::CLOCKWORK_PATH . '/';
    }
}
