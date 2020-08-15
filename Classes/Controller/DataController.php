<?php

declare(strict_types=1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\DataProvider\CountryZonesDataProvider;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

use function json_encode;

class DataController extends ActionController
{
    /**
     * @var CountryZonesDataProvider
     */
    protected $countryZonesDataProvider;

    public function injectCountryZonesDataProvider(CountryZonesDataProvider $countryZonesDataProvider)
    {
        $this->countryZonesDataProvider = $countryZonesDataProvider;
    }


    public function getStatesForCountryAction(string $country): string
    {
        $countryZones = $this->countryZonesDataProvider->getCountryZonesForCountryIso3($country);
        $jsonData = [];
        foreach ($countryZones as $countryZone) {
            $jsonData[$countryZone->getIsoCode()] = $countryZone->getLocalName();
        }

        return json_encode($jsonData);
    }
}
