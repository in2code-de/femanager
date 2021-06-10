<?php

declare(strict_types = 1);

namespace In2code\Femanager\UserFunc;

use In2code\Femanager\DataProvider\CountryDataProvider;
use In2code\Femanager\DataProvider\CountryZonesDataProvider;
use In2code\Femanager\DataProvider\FallbackCountryDataProvider;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class StaticInfoTables
{
    public function isStaticInfoTablesInstalled(): bool
    {
        return ExtensionManagementUtility::isLoaded('static_info_tables');
    }

    public function getStatesOptions(array $data, TcaSelectItems $tcaSelectItems)
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
                        ''
                    ]
                ];
            } else {
                $countryZones = $countryZonesDataProvider->getCountryZonesForCountryIso3($country);

                if (empty($countryZones)) {
                    $data['items'] = [
                        [
                            'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:noZonesForThisCountry',
                            ''
                        ]
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

    public function getCountryOptions(array $data, TcaSelectItems $tcaSelectItems)
    {
        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $countryDataProvider = GeneralUtility::makeInstance(CountryDataProvider::class);
            $countries = $countryDataProvider->getCountries();
            foreach ($countries as $country) {
                $data['items'][] = [$country->getShortNameEn(), $country->getIsoCodeA3()];
            }
        } else {
            $fallbackCountryDataProvider = GeneralUtility::makeInstance(FallbackCountryDataProvider::class);
            $countries = $fallbackCountryDataProvider->getCountries();
            foreach ($countries as $key => $name) {
                $data['items'][] = [$name, $key];
            }
        }
    }
}
