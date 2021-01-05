<?php

declare(strict_types=1);

namespace Fom\Clockwork\App\Response\HeaderProvider;

use Fom\Clockwork\Controller\Router;
use Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface;

class ClockworkPath implements HeaderProviderInterface
{
    private const HEADER_NAME = 'X-Clockwork-Path';

    use HeaderProviderTrait;

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
