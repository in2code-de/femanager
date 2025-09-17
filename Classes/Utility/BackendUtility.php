<?php

declare(strict_types=1);

namespace In2code\Femanager\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Class BackendUtility
 */
class BackendUtility
{
    public static function getPageIdentifier(): int
    {
        return (int)($GLOBALS['TYPO3_REQUEST']->getQueryParams()['id'] ?? null);
    }

    /**
     * Get URI to edit a record in backend
     *
     * @param string $tableName like "fe_users"
     * @param int $identifier record identifier to edit
     * @param bool $addReturnUrl add current URI as returnUrl
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function getBackendEditUri(string $tableName, int $identifier, bool $addReturnUrl = true): string
    {
        $uriParameters = [
            'edit' => [
                $tableName => [
                    $identifier => 'edit',
                ],
            ],
        ];
        if ($addReturnUrl) {
            $uriParameters['returnUrl'] = GeneralUtility::getIndpEnv('REQUEST_URI');
        }

        return (string)GeneralUtility::makeInstance(UriBuilder::class)
            ->buildUriFromRoute('record_edit', $uriParameters);
    }

    /**
     * Get URI to create a new record in backend
     *
     * @param string $tableName like "fe_users"
     * @param int $pageIdentifier page identifier to store the new record in
     * @param bool $addReturnUrl add current URI as returnUrl
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function getBackendNewUri(string $tableName, int $pageIdentifier, bool $addReturnUrl = true): string
    {
        $uriParameters = [
            'edit' => [
                $tableName => [
                    $pageIdentifier => 'new',
                ],
            ],
        ];
        if ($addReturnUrl) {
            // @codeCoverageIgnoreStart
            $uriParameters['returnUrl'] = GeneralUtility::getIndpEnv('REQUEST_URI');
            // @codeCoverageIgnoreEnd
        }

        return (string)GeneralUtility::makeInstance(UriBuilder::class)
            ->buildUriFromRoute('record_edit', $uriParameters);
    }

    /**
     * @return string "plugin" or "module"
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getPluginOrModuleString(): string
    {
        if (($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
            && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
            return 'module';
        }

        return 'plugin';
    }

    /**
     * Get all GET/POST params without module name and token
     */
    protected static function getCurrentParameters(): array
    {
        $parameters = [];
        $ignoreKeys = [
            'M',
            'moduleToken',
        ];
        foreach ((array)$GLOBALS['TYPO3_REQUEST']->getQueryParams() as $key => $value) {
            if (in_array($key, $ignoreKeys)) {
                continue;
            }

            $parameters[$key] = $value;
        }

        return $parameters;
    }

    /**
     * @param int $pageUid [optional] the current pageuid
     * @return array
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function loadTS($pageUid = null)
    {
        $pageUid = ($pageUid && MathUtility::canBeInterpretedAsInteger($pageUid)) ?
            $pageUid : $GLOBALS['TYPO3_REQUEST']->getParsedBody()['id'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['id'] ?? null;

        $typoScriptHandlerServer = GeneralUtility::makeInstance(TypoScriptUtility::class);
        return $typoScriptHandlerServer->getTypoScript($pageUid);
    }
}
