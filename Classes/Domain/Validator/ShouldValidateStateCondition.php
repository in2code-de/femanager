<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Validator;

use In2code\Femanager\DataProvider\CountryZonesDataProvider;
use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\Exception\PropertyNotAccessibleException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class ShouldValidateStateCondition implements ValidationConditionInterface
{
    protected bool $isStaticInfoTablesLoaded;

    protected ?CountryZonesDataProvider $countryZonesDataProvider = null;

    public function __construct()
    {
        $this->isStaticInfoTablesLoaded = ExtensionManagementUtility::isLoaded('static_info_tables');
        if ($this->isStaticInfoTablesLoaded) {
            $this->countryZonesDataProvider = GeneralUtility::makeInstance(CountryZonesDataProvider::class);
        }
    }

    /**
     * @throws PropertyNotAccessibleException
     */
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
