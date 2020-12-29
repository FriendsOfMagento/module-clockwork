<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Provider;

use Clockwork\Helpers\StackFilter;

class Serializer
{
    /**
     * Maximum depth of serialized multi-level arrays and objects
     *
     * @var int
     */
    private $serializationDepth;

    /**
     * A list of classes that will never be serialized (eg. a common service container class)
     *
     * @var string[]
     */
    private $serializationBlackList;

    /**
     * Enable or disable collecting of stack traces
     *
     * @var bool
     */
    private $traceIsEnabled;

    /**
     * Limit the number of frames to be collected
     *
     * @var int
     */
    private $traceLimit;

    /**
     * List of vendor names to skip when determining caller, common vendor are automatically added
     *
     * @var string[]
     */
    private $traceSkipVendors;

    /**
     * List of namespaces to skip when determining caller
     *
     * @var string[]
     */
    private $traceSkipNamespaces;

    /**
     * List of function names to skip when determining caller
     *
     * @var string[]
     */
    private $traceSkipFunctions;

    /**
     * List of class names to skip when determining caller
     *
     * @var string[]
     */
    private $traceSkipClasses;

    /**
     * @param int $serializationDepth
     * @param array $serializationBlackList
     * @param bool $traceIsEnabled
     * @param int $traceLimit
     * @param array $traceSkipVendors
     * @param array $traceSkipNamespaces
     * @param array $traceSkipFunctions
     * @param array $traceSkipClasses
     */
    public function __construct(
        int $serializationDepth = 10,
        array $serializationBlackList = [],
        bool $traceIsEnabled = true,
        int $traceLimit = 10,
        array $traceSkipVendors = [],
        array $traceSkipNamespaces = [],
        array $traceSkipFunctions = [],
        array $traceSkipClasses = []
    ) {
        $this->serializationDepth = $serializationDepth;
        $this->serializationBlackList = $serializationBlackList;
        $this->traceIsEnabled = $traceIsEnabled;
        $this->traceLimit = $traceLimit;
        $this->traceSkipVendors = $traceSkipVendors;
        $this->traceSkipNamespaces = $traceSkipNamespaces;
        $this->traceSkipFunctions = $traceSkipFunctions;
        $this->traceSkipClasses = $traceSkipClasses;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return [
            'limit' => $this->serializationDepth,
            'blackbox' => $this->serializationBlackList,
            'traces' => $this->traceIsEnabled,
            'tracesLimit' => $this->traceLimit,
            'tracesSkip' => $this->getStackTraceFilter(),
        ];
    }

    /**
     * @return StackFilter
     */
    private function getStackTraceFilter(): StackFilter
    {
        return StackFilter::make()
            ->isNotVendor($this->traceSkipVendors)
            ->isNotNamespace($this->traceSkipNamespaces)
            ->isNotFunction($this->traceSkipFunctions)
            ->isNotClass($this->traceSkipClasses);
    }
}
