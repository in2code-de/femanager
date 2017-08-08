<?php
declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Form;

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetCountriesFromStaticInfoTablesViewHelper
 */
class GetCountriesFromStaticInfoTablesViewHelper extends AbstractViewHelper
{

    /**
     * countryRepository
     *
     * @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository
     * @inject
     */
    protected $countryRepository;

    /**
     * Build an country array
     *
     * @param string $key
     * @param string $value
     * @param string $sortbyField
     * @param string $sorting
     * @return array
     */
    public function render(
        $key = 'isoCodeA3',
        $value = 'officialNameLocal',
        $sortbyField = 'isoCodeA3',
        $sorting = 'asc'
    ): array {
        $countries = $this->countryRepository->findAllOrderedBy($sortbyField, $sorting);
        $countriesArray = [];
        foreach ($countries as $country) {
            /** @var $country \SJBR\StaticInfoTables\Domain\Model\Country */
            $countriesArray[ObjectAccess::getProperty($country, $key)] = ObjectAccess::getProperty($country, $value);
        }
        return $countriesArray;
    }
}
