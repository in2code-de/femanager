<?php

declare(strict_types=1);

namespace In2code\Femanager\DataProvider;

use SJBR\StaticInfoTables\Domain\Model\Country;
use SJBR\StaticInfoTables\Domain\Repository\CountryRepository;

class CountryDataProvider
{
    protected $countryRepository;

    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    /** @return Country[] */
    public function getCountries(): array
    {
        return $this->countryRepository->findAllOrderedBy('shortNameLocal')->toArray();
    }
}
