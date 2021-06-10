<?php
declare(strict_types = 1);

namespace In2code\Femanager\ViewHelpers\Form;

use SJBR\StaticInfoTables\Domain\Repository\CountryRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetCountriesFromStaticInfoTablesViewHelper
 */
class GetCountriesFromStaticInfoTablesViewHelper extends AbstractViewHelper
{
    /**
     * @var CountryRepository
     */
    protected $countryRepository = null;

    public function __construct()
    {
        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $this->countryRepository = GeneralUtility::makeInstance(CountryRepository::class);
        }
    }

    /**
     * Build an country array
     *
     * @param string $sorting
     * @return array
     */
    public function render(): array
    {
        if (null === $this->countryRepository) {
            return ['ERROR: static_info_tables is not loaded'];
        }
        $key = $this->arguments['key'];
        $value = $this->arguments['value'];
        $sortbyField = $this->arguments['sortbyField'];
        $sorting = $this->arguments['sorting'];

        $countries = $this->countryRepository->findAllOrderedBy($sortbyField, $sorting);
        $countriesArray = [];
        foreach ($countries as $country) {
            /** @var $country \SJBR\StaticInfoTables\Domain\Model\Country */
            $countriesArray[ObjectAccess::getProperty($country, $key)] = ObjectAccess::getProperty($country, $value);
        }

        return $countriesArray;
    }

    /**
     * Initialize
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('key', 'string', 'country isoCode', false, 'isoCodeA3');
        $this->registerArgument('value', 'string', 'officialNameLocal', false, 'officialNameLocal');
        $this->registerArgument('sortbyField', 'string', 'isoCodeA3', false, 'isoCodeA3');
        $this->registerArgument('sorting', 'string', 'value to prepend', false, 'asc');
    }
}
