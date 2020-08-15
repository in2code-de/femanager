<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Validator;

use In2code\Femanager\DataProvider\CountryZonesDataProvider;
use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository;
use SJBR\StaticInfoTables\Domain\Repository\CountryRepository;

class ShouldValidateStateCondition implements ValidationConditionInterface
{
    protected $isStaticInfoTablesLoaded;

    protected $countryZonesDataProvider;

    /**
     * @var \SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository
     */
    protected $countryZoneRepository;

    public function injectCountryZoneRepository(CountryZoneRepository $countryZoneRepository)
    {
        $this->countryZoneRepository = $countryZoneRepository;
    }

    public function injectCountryRepository(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    /**
     * @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository
     */
    protected $countryRepository;

    public function __construct()
    {
        $this->isStaticInfoTablesLoaded = ExtensionManagementUtility::isLoaded('static_info_tables');
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        if ($this->isStaticInfoTablesLoaded) {
            $this->countryZonesDataProvider = $objectManager->get(CountryZonesDataProvider::class);
        }
    }



    public function shouldValidate(User $user, string $fieldName, array $validationSettings): bool
    {
        return $this->isStaticInfoTablesLoaded
            && $this->countryHasZones(ObjectAccess::getProperty($user, 'country'));
    }

    protected function countryHasZones($countryIso3)
    {
        return $this->countryZonesDataProvider->hasCountryZonesForCountryIso3($countryIso3);
    }
}
