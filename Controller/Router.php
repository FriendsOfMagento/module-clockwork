<?php

declare(strict_types=1);

namespace Fom\Clockwork\Controller;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\Router\ActionList;
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
     * @var ActionList
     */
    private $actionList;

    /**
     * @param ActionFactory $actionFactory
     * @param ActionList $actionList
     */
    public function __construct(
        ActionFactory $actionFactory,
        ActionList $actionList
    ) {
        $this->actionFactory = $actionFactory;
        $this->actionList = $actionList;
    }

    /**
     * @param RequestInterface|Http $request
     *
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request): ?ActionInterface
    {
        // /__clockwork/{id}
        $actionInstance = null;
        if ($this->isClockworkPath($request) && $clockworkId = $this->getClockworkId($request)) {
            $request
                ->setRouteName('fom_clockwork')
                ->setControllerName('report')
                ->setActionName('view')
                ->setParam('id', $clockworkId);

            $actionInstance = $this->actionFactory->create(Forward::class);
        }

        // TODO: /__clockwork/{id}/extended?only=xdebug
        // TODO: /__clockwork/{id}?only=clientMetrics%2CwebVitals

        // TODO: /__clockwork/latest
        // TODO: /__clockwork/{id}|latest/next/{limit?}
        // TODO: /__clockwork/{id}|latest/previous/{limit?}

        return $actionInstance;
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
     * @return string
     */
    private function getClockworkId(RequestInterface $request): string
    {
        [, , $clockworkId] = array_pad(explode('/', $request->getPathInfo(), 3), 3, null);

        return (string)$clockworkId;
    }
}
