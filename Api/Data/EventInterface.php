<?php

declare(strict_types=1);

namespace Fom\Clockwork\Api\Data;

interface EventInterface
{
    public const DESCRIPTION = 'description';
    public const START = 'start';
    public const END = 'end';
    public const DURATION = 'duration';
    public const COLOR = 'color';
    public const DATA = 'data';
}
