<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Form;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetCountriesViewHelper
 */
class GetCountriesViewHelper extends AbstractViewHelper
{
    public function __construct(
        private readonly CountryProvider $countryProvider,
        private readonly LanguageServiceFactory $languageServiceFactory
    ) {
    }

    /**
     * Build a country array
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function render(): array
    {
        /**
         * @var ServerRequestInterface $request
         */
        $request = $GLOBALS['TYPO3_REQUEST'];
        $languageService = null;
        if ($request->getAttribute('language') instanceof SiteLanguage) {
            $languageService =
                $this->languageServiceFactory->createFromSiteLanguage($request->getAttribute('language'));
        }

        $returnArray = [];
        $countries = $this->countryProvider->getAll();
        foreach ($countries as $country) {
            $returnArray[$country->getAlpha3IsoCode()] =
                $languageService instanceof \TYPO3\CMS\Core\Localization\LanguageService ?
                    $languageService->sL($country->getLocalizedNameLabel()) :
                    $country->getName();
        }

        asort($returnArray);
        return $returnArray;
    }
}
