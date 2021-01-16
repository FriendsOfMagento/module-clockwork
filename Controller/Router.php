<?php

declare(strict_types=1);

namespace Fom\Clockwork\Controller;

use Fom\Clockwork\Model\Config;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Action\Forward;

class Router implements RouterInterface
{
    /**
     * Url path.
     */
    public const CLOCKWORK_PATH = '/__clockwork';

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ActionFactory $actionFactory
     * @param Config $config
     */
    public function __construct(
        ActionFactory $actionFactory,
        Config $config
    ) {
        $this->actionFactory = $actionFactory;
        $this->config = $config;
    }

    /**
     * @param RequestInterface|Http $request
     *
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request): ?ActionInterface
    {
        $matched = false;
        if ($this->isClockworkWebPath($request)) {
            $request->setRouteName('fom_clockwork')
                ->setControllerName('index')
                ->setActionName('index');
            $matched = true;
        } elseif ($this->isClockworkPath($request)) {
            $request->setRouteName('fom_clockwork')
                ->setControllerName('report')
                ->setActionName('view')
                ->setParam('id', $this->getClockworkId($request))
                ->setParam('extended', $this->isExtended($request));
            foreach ($this->getParams($request) as $param => $value) {
                $request->setParam($param, $value);
            }

            $matched = true;
        }

        return $matched ? $this->actionFactory->create(Forward::class) : null;
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    private function isClockworkWebPath(RequestInterface $request): bool
    {
        return in_array($this->config->getWebPath(), explode('/', $request->getPathInfo(), 1));
    }

    /**
     * @param RequestInterface|Http $request
     *
     * @return bool
     */
    private function isClockworkPath(RequestInterface $request): bool
    {
        return strpos($request->getPathInfo(), self::CLOCKWORK_PATH) === 0;
    }

    /**
     * @param RequestInterface $request
     *
     * @return array
     */
    private function getParams(RequestInterface $request): array
    {
        [, , , $direction, $count] = array_pad(explode('/', $request->getPathInfo()), 5, null);

        return [
            'direction' => $direction,
            'count' => $count ? (int)$count : null,
            'only' => $request->getParam('only'),
        ];
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    private function isExtended(RequestInterface $request): bool
    {
        [, , , $extended] = array_pad(explode('/', $request->getPathInfo()), 4, null);

        return $extended === 'extended';
    }

    /**
     * @param RequestInterface $request
     *
     * @return string
     */
    private function getClockworkId(RequestInterface $request): string
    {
        [, , $clockworkId] = array_pad(explode('/', $request->getPathInfo()), 3, null);

        return (string)$clockworkId;
    }
}
