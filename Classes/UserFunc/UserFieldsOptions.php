<?php

declare(strict_types=1);

namespace In2code\Femanager\UserFunc;

use In2code\Femanager\Utility\StringUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UserFieldsOptions
 */
class UserFieldsOptions
{
    /**
     * @var LanguageService
     */
    protected $languageService;

    /**
     * @var string
     */
    protected $localLangPrefix = 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:';

    /**
     * Add options to FlexForm Selection - Options can be defined in TSConfig
     */
    public function addOptions(array &$params): void
    {
        $tSconfig = BackendUtility::getPagesTSconfig($params['effectivePid'] ?? 0);
        $this->addCaptchaOption($params);
        $this->addStateOption($params);
        $tab = $params['config']['itemsProcFuncTab'] . '.';
        if (!empty($tSconfig['tx_femanager.']['flexForm.'][$tab]['addFieldOptions.'])) {
            $options = $tSconfig['tx_femanager.']['flexForm.'][$tab]['addFieldOptions.'];
            foreach ((array)$options as $value => $label) {
                $params['items'][] = [
                    StringUtility::startsWith($label, 'LLL:') ? $this->languageService->sL($label) : $label,
                    $value,
                ];
            }
        }
    }

    /**
     * Add captcha option
     */
    protected function addCaptchaOption(array &$params)
    {
        if (ExtensionManagementUtility::isLoaded('sr_freecap')) {
            $params['items'][] = [
                $this->languageService->sL($this->localLangPrefix . 'tx_femanager_domain_model_user.captcha'),
                'captcha',
            ];
        }
    }

    /**
     * Add captcha option
     */
    protected function addStateOption(array &$params)
    {
        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $params['items'][] = [
                $this->languageService->sL($this->localLangPrefix . 'tx_femanager_domain_model_user.state'),
                'state',
            ];
        }
    }

    /**
     * Initialize
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function initialize()
    {
        $this->languageService = $GLOBALS['LANG'];
    }
}
