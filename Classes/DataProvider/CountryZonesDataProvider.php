<?php

declare(strict_types = 1);

namespace In2code\Femanager\DataProvider;

use SJBR\StaticInfoTables\Domain\Model\Country;
use SJBR\StaticInfoTables\Domain\Model\CountryZone;
use SJBR\StaticInfoTables\Domain\Repository\CountryRepository;
use SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CountryZonesDataProvider
{
    private $countryZoneRepository;

    private $countryRepository;

    public function __construct()
    {
        // Can not autowire optional dependencies
        $this->countryZoneRepository = GeneralUtility::makeInstance(CountryZoneRepository::class);
        $this->countryRepository = GeneralUtility::makeInstance(CountryRepository::class);
    }

    /** @return CountryZone[] */
    public function getCountryZonesForCountryIso3(string $countryIso3): array
    {
        $country = $this->getCountryForIsoCode3($countryIso3);
        if (null === $country) {
            return [];
        }
        $countryZones = $this->countryZoneRepository->findByCountry($country)->toArray();
        usort(
            $countryZones,
            function (CountryZone $left, CountryZone $right) {
                return strcasecmp($left->getLocalName(), $right->getLocalName());
            }
        );
        return $countryZones;
    }

    public function hasCountryZonesForCountryIso3(string $countryIso3): bool
    {
        return $this->countryZoneRepository->countByCountryIsoCodeA3($countryIso3) > 0;
    }

    /**
     * @param string $countryIso3
     * @return Country|null
     */
    private function getCountryForIsoCode3(string $countryIso3): ?Country
    {
        $countries = $this->countryRepository->findAllowedByIsoCodeA3($countryIso3);
        $country = null;
        if (count($countries) === 1) {
            $country = array_values($countries)[0];
        }
        return $country;
    }
}
