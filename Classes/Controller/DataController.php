<?php

declare(strict_types=1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\DataProvider\CountryZonesDataProvider;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class DataController extends ActionController
{
    protected $countryZonesDataProvider;

    public function __construct(CountryZonesDataProvider $countryZonesDataProvider)
    {
        $this->countryZonesDataProvider = $countryZonesDataProvider;
    }

    public function getStatesForCountryAction(string $country): ResponseInterface
    {
        $countryZones = $this->countryZonesDataProvider->getCountryZonesForCountryIso3($country);
        $jsonData = [];
        foreach ($countryZones as $countryZone) {
            $jsonData[$countryZone->getIsoCode()] = $countryZone->getLocalName();
        }

        return $this->jsonResponse(json_encode($jsonData, JSON_THROW_ON_ERROR));
    }
}
