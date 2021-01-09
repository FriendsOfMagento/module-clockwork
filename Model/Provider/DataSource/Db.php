<?php

declare(strict_types=1);

namespace Fom\Clockwork\Model\Provider\DataSource;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Fom\Clockwork\Db\Logger as DbLogger;

class Db extends DataSource
{
    /**
     * @param Request $request
     *
     * @return Request
     */
    public function resolve(Request $request): Request
    {
        $request->databaseQueries = array_merge($request->databaseQueries, DbLogger::getQueries());

        return $request;
    }
}
