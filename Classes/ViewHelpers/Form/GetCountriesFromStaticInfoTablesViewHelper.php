<?php
declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Form;

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetCountriesFromStaticInfoTablesViewHelper
 */
class GetCountriesFromStaticInfoTablesViewHelper extends AbstractViewHelper
{

    /**
     * countryRepository
     *
     * @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $countryRepository;

    /**
     * Build an country array
     *
     * @param string $sorting
     * @return array
     */
    public function render(): array
    {
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
     *
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('key', 'string', 'country isoCode', false, 'isoCodeA3');
        $this->registerArgument('value', 'string', 'shortNameLocal', false, 'shortNameLocal');
        $this->registerArgument('sortbyField', 'string', 'shortNameLocal', false, 'shortNameLocal');
        $this->registerArgument('sorting', 'string', 'value to prepend', false, 'asc');
    }
}
