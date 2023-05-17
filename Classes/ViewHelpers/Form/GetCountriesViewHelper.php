<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Form;

use In2code\Femanager\DataProvider\FallbackCountryDataProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetCountriesViewHelper
 */
class GetCountriesViewHelper extends AbstractViewHelper
{
    /**
     * Build a country array
     */
    public function render(): array
    {
        return GeneralUtility::makeInstance(FallbackCountryDataProvider::class)->getCountries();
    }
}
