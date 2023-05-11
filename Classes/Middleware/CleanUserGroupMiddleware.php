<?php

declare(strict_types=1);

namespace In2code\Femanager\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CleanUserGroupMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestBody = $request->getParsedBody();
        if (
            is_array($requestBody['tx_femanager_registration']['user']['usergroup'] ?? null)
            && empty($requestBody['tx_femanager_registration']['user']['usergroup'][0])
            && empty($requestBody['tx_femanager_registration']['user']['usergroup']['__identity'])
        ) {
            unset($requestBody['tx_femanager_registration']['user']['usergroup'][0]);
        }
        $request = $request->withParsedBody($requestBody);
        return $handler->handle($request);
    }
}
