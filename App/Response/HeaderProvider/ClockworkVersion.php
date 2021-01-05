<?php

declare(strict_types=1);

namespace Fom\Clockwork\App\Response\HeaderProvider;

use Clockwork\Clockwork;
use Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface;

class ClockworkVersion implements HeaderProviderInterface
{
    private const HEADER_NAME = 'X-Clockwork-Vesrion';

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
        return Clockwork::VERSION;
    }
}
