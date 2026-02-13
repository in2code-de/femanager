<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Form;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetCountriesViewHelper extends AbstractViewHelper
{
    public function __construct(
        private readonly CountryProvider $countryProvider,
        private readonly LanguageServiceFactory $languageServiceFactory
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function render(): array
    {
        $request = $this->getRequest();
        if (!$request) {
            return [];
        }

        $languageService = $this->getLanguageService($request);
        $countries = $this->countryProvider->getAll();
        $options = [];

        foreach ($countries as $country) {
            $label = $languageService?->sL($country->getLocalizedNameLabel()) ?: $country->getName();
            $options[$country->getAlpha3IsoCode()] = $label;
        }

        asort($options);
        return $options;
    }

    private function getLanguageService(ServerRequestInterface $request): ?LanguageService
    {
        $language = $request->getAttribute('language');
        if ($language instanceof SiteLanguage) {
            return $this->languageServiceFactory->createFromSiteLanguage($language);
        }
        return null;
    }

    private function getRequest(): ServerRequestInterface|null
    {
        if ($this->renderingContext->hasAttribute(ServerRequestInterface::class)) {
            return $this->renderingContext->getAttribute(ServerRequestInterface::class);
        }
        return null;
    }
}
