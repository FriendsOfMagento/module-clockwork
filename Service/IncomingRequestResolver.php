<?php

declare(strict_types=1);

namespace Fom\Clockwork\Service;

use Clockwork\Request\IncomingRequest;
use Clockwork\Request\IncomingRequestFactory;
use Magento\Framework\App\RequestInterface;

class IncomingRequestResolver
{
    /**
     * Keys for init_parameter values.
     */
    public const KEY_REQUEST_METHOD = 'REQUEST_METHOD';
    public const KEY_REQUEST_URI = 'REQUEST_URI';

    /**
     * Profiler param and cookie name.
     */
    private const PROFILE_NAME = 'clockwork-profile';

    /**
     * @var IncomingRequestFactory
     */
    private $incomingRequestFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private $requestMethod;

    /**
     * @var string
     */
    private $requestUri;

    /**
     * @param IncomingRequestFactory $incomingRequestFactory
     * @param RequestInterface $request
     * @param string $requestMethod
     * @param string $requestUri
     */
    public function __construct(
        IncomingRequestFactory $incomingRequestFactory,
        RequestInterface $request,
        string $requestMethod,
        string $requestUri
    ) {
        $this->incomingRequestFactory = $incomingRequestFactory;
        $this->request = $request;
        $this->requestMethod = $requestMethod;
        $this->requestUri = $requestUri;
    }

    /**
     * @return IncomingRequest
     */
    public function resolve(): IncomingRequest
    {
        return $this->incomingRequestFactory->create(
            [
                'data' => [
                    'method' => $this->requestMethod,
                    'uri' => $this->requestUri,
                    'input' => $this->getInput(),
                    'cookies' => $this->getCookies(),
                ]
            ]
        );
    }

    /**
     * @return array
     */
    private function getInput(): array
    {
        return [
            self::PROFILE_NAME => $this->request->getParam(self::PROFILE_NAME, ''),
        ];
    }

    /**
     * @return array
     */
    private function getCookies(): array
    {
        return [
            self::PROFILE_NAME => $this->request->getCookie(self::PROFILE_NAME, ''),
        ];
    }
}
