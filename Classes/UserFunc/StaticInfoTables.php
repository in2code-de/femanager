<?php

declare(strict_types=1);

namespace In2code\Femanager\UserFunc;

use Collator;
use In2code\Femanager\DataProvider\CountryDataProvider;
use In2code\Femanager\DataProvider\CountryZonesDataProvider;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Schema\Struct\SelectItem;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class StaticInfoTables
{
    protected LanguageService $languageService;
    protected string $localLangPrefix = 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:';

    public function __construct(
        private readonly CountryProvider $countryProvider,
        protected readonly LanguageServiceFactory $languageServiceFactory
    ) {
        $this->languageService = $this->languageServiceFactory->create($GLOBALS['BE_USER']->uc['lang'] ?? 'default');
    }

    public function getStatesOptions(array &$data): void
    {
        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $data['items'][] = new SelectItem('select', 'Error: static_info_tables is not installed', '');
            return;
        }
        $country = $this->extractCountryValue($data['row']['country'] ?? null);

        if (empty($country)) {
            $data['items'] = [new SelectItem('select', $this->localLangPrefix . 'pleaseChooseCountry', '')];
            return;
        }

        $countryZonesDataProvider = GeneralUtility::makeInstance(CountryZonesDataProvider::class);
        $countryZones = $countryZonesDataProvider->getCountryZonesForCountryIso3($country);

        if (empty($countryZones)) {
            $data['items'][] = new SelectItem('select', $this->localLangPrefix . 'noZonesForThisCountry', '');
            return;
        }

        foreach ($countryZones as $countryZone) {
            $data['items'][] = new SelectItem('select', $countryZone->getLocalName(), $countryZone->getIsoCode());
        }
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getCountryOptions(array &$data): void
    {
        $items = [];

        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $countryDataProvider = GeneralUtility::makeInstance(CountryDataProvider::class);
            foreach ($countryDataProvider->getCountries() as $country) {
                $items[] = new SelectItem('select', $country->getShortNameLocal(), $country->getIsoCodeA3());
            }
        } else {
            foreach ($this->countryProvider->getAll() as $country) {
                $items[] = new SelectItem(
                    'select',
                    $this->languageService->sL($country->getLocalizedNameLabel()),
                    $country->getAlpha3IsoCode()
                );
            }
        }

        $this->sortSelectItems($items);
        $data['items'] = array_merge($data['items'], $items);
    }

    private function extractCountryValue(mixed $country): ?string
    {
        if (is_array($country)) {
            return (string)(array_values($country)[0] ?? '');
        }
        return $country ? (string)$country : null;
    }

    private function sortSelectItems(array &$items): void
    {
        $collator = new Collator((string)$this->languageService->getLocale() ?: 'en');
        usort($items, static function (SelectItem $a, SelectItem $b) use ($collator) {
            return $collator->compare($a->getLabel(), $b->getLabel());
        });
    }
}
