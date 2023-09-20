<?php

declare(strict_types=1);

namespace In2code\Femanager\DataProvider;

use SJBR\StaticInfoTables\Domain\Repository\CountryRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CountryDataProvider
{
    protected $countryRepository;

    public function __construct()
    {
        /* @phpstan-ignore-next-line */
        $this->countryRepository = GeneralUtility::makeInstance(CountryRepository::class);
    }

    public function getCountries(): array
    {
        return $this->countryRepository->findAllOrderedBy('shortNameLocal')->toArray();
    }
}
