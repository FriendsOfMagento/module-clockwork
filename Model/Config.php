<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model;

use Fom\Clockwork\Controller\Router;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    /**
     * The configuration path for the value "Clockwork / General Configuration / Web Path".
     */
    private const GENERAL_WEB_PATH = 'fom_clockwork/general/web_path';

    /**
     * The configuration path for the value "Clockwork / Request Configuration / Collect Data Always".
     */
    private const REQUEST_COLLECT_DATA_ALWAYS = 'fom_clockwork/request/collect_data_always';

    /**
     * The configuration path for the value "Clockwork / Request Configuration / On Demand".
     */
    private const REQUEST_ON_DEMAND = 'fom_clockwork/request/on_demand';

    /**
     * The configuration path for the value "Clockwork / Request Configuration / Errors Only".
     */
    private const REQUEST_ERRORS_ONLY = 'fom_clockwork/request/errors_only';

    /**
     * The configuration path for the value "Clockwork / Request Configuration / Slow Only".
     */
    private const REQUEST_SLOW_ONLY = 'fom_clockwork/request/slow_only';

    /**
     * The configuration path for the value "Clockwork / Request Configuration / Slow Threshold".
     */
    private const REQUEST_SLOW_THRESHOLD = 'fom_clockwork/request/slow_threshold';

    /**
     * The configuration path for the value "Clockwork / Request Configuration / Sample".
     */
    private const REQUEST_SAMPLE = 'fom_clockwork/request/sample';

    /**
     * The configuration path for the value "Clockwork / Request Configuration / Except URIs".
     */
    private const REQUEST_EXCEPT_URI = 'fom_clockwork/request/except_uri';

    /**
     * The configuration path for the value "Clockwork / Request Configuration / Only URIs".
     */
    private const REQUEST_ONLY_URI = 'fom_clockwork/request/only_uri';

    /**
     * The configuration path for the value "Clockwork / Request Configuration / Except Preflight".
     */
    private const REQUEST_EXCEPT_PREFLIGHT = 'fom_clockwork/request/except_preflight';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getWebPath(): string
    {
        return (string)$this->scopeConfig->getValue(self::GENERAL_WEB_PATH);
    }

    /**
     * @return bool
     */
    public function canCollectAlways(): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(self::REQUEST_COLLECT_DATA_ALWAYS);
    }

    /**
     * @return bool
     */
    public function isOnDemand(): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(self::REQUEST_ON_DEMAND);
    }

    /**
     * @return bool
     */
    public function isErrorsOnly(): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(self::REQUEST_ERRORS_ONLY);
    }

    /**
     * @return bool
     */
    public function isSlowOnly(): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(self::REQUEST_SLOW_ONLY);
    }

    /**
     * @return int|null
     */
    public function getSlowThreshold(): ?int
    {
        return (int)$this->scopeConfig->getValue(self::REQUEST_SLOW_THRESHOLD) ?: null;
    }

    /**
     * @return int
     */
    public function getSampleCount(): int
    {
        return (int)$this->scopeConfig->getValue(self::REQUEST_SAMPLE);
    }

    /**
     * @return array
     */
    public function getExceptUriList(): array
    {
        return array_merge(
            $this->getUriList(self::REQUEST_EXCEPT_URI),
            [Router::CLOCKWORK_PATH, $this->getWebPath()]
        );
    }

    /**
     * @return array
     */
    public function getOnlyUriList(): array
    {
        return $this->getUriList(self::REQUEST_ONLY_URI);
    }

    /**
     * @param string $configPath
     *
     * @return array
     */
    private function getUriList(string $configPath): array
    {
        $list = [];
        $items = $this->scopeConfig->getValue($configPath);
        if (!empty($items)) {
            foreach ($items as $item) {
                $list[] = $item['uri'] ?? null;
            }
        }

        $list = array_map('trim', $list);
        $list = array_unique($list);
        $list = array_filter($list);

        return $list;
    }

    /**
     * @return bool
     */
    public function isExceptPreflight(): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(self::REQUEST_EXCEPT_PREFLIGHT);
    }
}
