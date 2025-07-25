<?php

declare(strict_types=1);

namespace In2code\Femanager\UserFunc;

use In2code\Femanager\DataProvider\CountryDataProvider;
use In2code\Femanager\DataProvider\CountryZonesDataProvider;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class StaticInfoTables
{
    public function __construct(
        private readonly CountryProvider $countryProvider
    ) {
    }

    public function isStaticInfoTablesInstalled(): bool
    {
        return ExtensionManagementUtility::isLoaded('static_info_tables');
    }

    public function getStatesOptions(array $data)
    {
        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $countryZonesDataProvider = GeneralUtility::makeInstance(CountryZonesDataProvider::class);
            $country = $data['row']['country'] ?? null;
            if (is_array($country)) {
                if (count($country) > 0) {
                    $country = array_values($country)[0];
                } else {
                    $country = null;
                }
            }
            if (empty($country)) {
                $data['items'] = [
                    [
                        'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:pleaseChooseCountry',
                        '',
                    ],
                ];
            } else {
                $countryZones = $countryZonesDataProvider->getCountryZonesForCountryIso3($country);

                if (empty($countryZones)) {
                    $data['items'] = [
                        [
                            'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:noZonesForThisCountry',
                            '',
                        ],
                    ];
                } else {
                    foreach ($countryZones as $countryZone) {
                        $data['items'][] = [$countryZone->getLocalName(), $countryZone->getIsoCode()];
                    }
                }
            }
        } else {
            $data['items'][] = ['Error: static_info_tables is not installed', ''];
        }
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getCountryOptions(array $data)
    {
        $items = [];

        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $countryDataProvider = GeneralUtility::makeInstance(CountryDataProvider::class);
            $countries = $countryDataProvider->getCountries();
            foreach ($countries as $country) {
                $items[] = [
                    $country->getShortNameEn(),
                    $country->getIsoCodeA3()
                ];
            }
        } else {
            $countries = $this->countryProvider->getAll();
            foreach ($countries as $country) {
                $items[] = [
                    $this->getLanguageService()->sL($country->getLocalizedNameLabel()),
                    $country->getAlpha3IsoCode(),
                ];
            }

            $locale = (string)($this->getLanguageService()->getLocale() ?? 'en');
            $collator = new \Collator($locale);
            usort($items, function(array $itemA, array $itemB) use ($collator) {
                return $collator->compare($itemA[0], $itemB[0]);
            });
        }

        $data['items'] = array_merge($data['items'], $items);
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
