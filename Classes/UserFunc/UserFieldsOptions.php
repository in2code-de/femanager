<?php

declare(strict_types=1);

namespace In2code\Femanager\UserFunc;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Schema\Struct\SelectItem;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class UserFieldsOptions
{
    protected LanguageService $languageService;
    protected string $localLangPrefix = 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:';

    public function __construct(protected readonly LanguageServiceFactory $languageServiceFactory)
    {
        $this->languageService = $this->languageServiceFactory->create('default');
    }

    /**
     * Add options to FlexForm Selection - Options can be defined in TSConfig
     */
    public function addOptions(array &$params): void
    {
        $this->addCaptchaOption($params);
        $this->addStateOption($params);

        $pid = (int)($params['effectivePid'] ?? 0);
        $tSconfig = BackendUtility::getPagesTSconfig($pid);

        $tab = $params['config']['itemsProcFuncTab'] ?? '';
        $fieldOptions = $tSconfig['tx_femanager.']['flexForm.'][$tab . '.']['addFieldOptions.'] ?? [];

        if (empty($fieldOptions)) {
            return;
        }

        foreach ($fieldOptions as $value => $label) {
            if (str_ends_with((string)$value, '.')) {
                continue;
            }

            $translatedLabel = str_starts_with((string)$label, 'LLL:')
                ? $this->languageService->sL($label)
                : $label;

            $params['items'][] = new SelectItem(
                'select',
                $translatedLabel,
                $value,
            );
        }
    }

    protected function addCaptchaOption(array &$params): void
    {
        if (ExtensionManagementUtility::isLoaded('sr_freecap')) {
            $params['items'][] = new SelectItem(
                'select',
                $this->languageService->sL($this->localLangPrefix . 'tx_femanager_domain_model_user.captcha'),
                'captcha'
            );
        }
    }

    protected function addStateOption(array &$params): void
    {
        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $params['items'][] = new SelectItem(
                'select',
                $this->languageService->sL($this->localLangPrefix . 'tx_femanager_domain_model_user.state'),
                'state'
            );
        }
    }
}
