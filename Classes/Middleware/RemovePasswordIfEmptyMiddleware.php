<?php

declare(strict_types=1);

namespace In2code\Femanager\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class RemovePasswordIfEmptyMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $typoscript = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,'femanager');
        $requestBody = $request->getParsedBody();

        if (!empty($typoscript['plugin.']['tx_femanager.']['settings.']['edit.']['misc.']['keepPasswordIfEmpty']) &&
            empty($requestBody['tx_femanager_edit']['user']['password']) &&
            empty($requestBody['tx_femanager_edit']['password_repeat'])) {
            $requestBody = $request->getParsedBody();
            unset($requestBody['tx_femanager_edit']['user']['password']);
            unset($requestBody['tx_femanager_edit']['password_repeat']);
            $request = $request->withParsedBody($requestBody);
        }

        return $handler->handle($request);
    }
}
