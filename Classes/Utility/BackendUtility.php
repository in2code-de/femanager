<?php
declare(strict_types=1);
namespace In2code\Femanager\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class BackendUtility
 */
class BackendUtility
{

    /**
     * @return int
     */
    public static function getPageIdentifier(): int
    {
        return (int)GeneralUtility::_GET('id');
    }

    /**
     * Get URI to edit a record in backend
     *
     * @param string $tableName like "fe_users"
     * @param int $identifier record identifier to edit
     * @param bool $addReturnUrl add current URI as returnUrl
     * @return string
     */
    public static function getBackendEditUri(string $tableName, int $identifier, bool $addReturnUrl = true): string
    {
        $uriParameters = [
            'edit' => [
                $tableName => [
                    $identifier => 'edit'
                ]
            ]
        ];
        if ($addReturnUrl) {
            $uriParameters['returnUrl'] =
                BackendUtilityCore::getModuleUrl(GeneralUtility::_GET('M'), self::getCurrentParameters());
        }
        return BackendUtilityCore::getModuleUrl('record_edit', $uriParameters);
    }

    /**
     * Get URI to create a new record in backend
     *
     * @param string $tableName like "fe_users"
     * @param int $pageIdentifier page identifier to store the new record in
     * @param bool $addReturnUrl add current URI as returnUrl
     * @return string
     */
    public static function getBackendNewUri(string $tableName, int $pageIdentifier, bool $addReturnUrl = true): string
    {
        $uriParameters = [
            'edit' => [
                $tableName => [
                    $pageIdentifier => 'new'
                ]
            ]
        ];
        if ($addReturnUrl) {
            // @codeCoverageIgnoreStart
            $uriParameters['returnUrl'] =
                BackendUtilityCore::getModuleUrl(GeneralUtility::_GET('M'), self::getCurrentParameters());
            // @codeCoverageIgnoreEnd
        }
        return BackendUtilityCore::getModuleUrl('record_edit', $uriParameters);
    }

    /**
     * @return string "plugin" or "module"
     */
    public static function getPluginOrModuleString(): string
    {
        $string = 'plugin';
        if (TYPO3_MODE === 'BE') {
            $string = 'module';
        }
        return $string;
    }

    /**
     * @param int $pageIdentifier
     * @param int $typeNum
     * @return bool
     * @SuppressWarnings(PHPMD.Superglobals)
     * @codeCoverageIgnore
     */
    public static function initializeTsFe(int $pageIdentifier = 0, int $typeNum = 0): bool
    {
        if (TYPO3_MODE === 'BE') {
            try {
                if (!empty(GeneralUtility::_GP('id'))) {
                    $pageIdentifier = (int)GeneralUtility::_GP('id');
                }
                if (!empty(GeneralUtility::_GP('type'))) {
                    $typeNum = (int)GeneralUtility::_GP('type');
                }
                if (!is_object($GLOBALS['TT'])) {
                    $GLOBALS['TT'] = new TimeTracker(false);
                    $GLOBALS['TT']->start();
                }
                $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                    TypoScriptFrontendController::class,
                    $GLOBALS['TYPO3_CONF_VARS'],
                    $pageIdentifier,
                    $typeNum
                );
                $GLOBALS['TSFE']->connectToDB();
                $GLOBALS['TSFE']->initFEuser();
                $GLOBALS['TSFE']->determineId();
                $GLOBALS['TSFE']->initTemplate();
                $GLOBALS['TSFE']->getConfigArray();
                return true;
            } catch (\Exception $exception) {
                /**
                 * Normally happens if $_GET['id'] points to a sysfolder on root
                 * In this case: Simply do not initialize TsFe
                 */
                return false;
            }
        }
        return false;
    }

    /**
     * Get all GET/POST params without module name and token
     *
     * @return array
     */
    protected static function getCurrentParameters(): array
    {
        $parameters = [];
        $ignoreKeys = [
            'M',
            'moduleToken'
        ];
        foreach ((array)GeneralUtility::_GET() as $key => $value) {
            if (in_array($key, $ignoreKeys)) {
                continue;
            }
            $parameters[$key] = $value;
        }
        return $parameters;
    }


    /**
     * @param int $pageUid [optional] the current pageuid
     * @return type
     */
    public static function loadTS($pageUid = null)
    {
        $pageUid = ($pageUid && \TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($pageUid)) ? $pageUid : \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id');
        $sysPageObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
        $TSObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\\TemplateService');
        $TSObj->tt_track = 0;
        $TSObj->init();
        $TSObj->runThroughTemplates($sysPageObj->getRootLine($pageUid));
        $TSObj->generateConfig();

        return $TSObj->setup;
    }
}

