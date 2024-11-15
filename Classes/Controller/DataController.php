<?php

declare(strict_types=1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\DataProvider\CountryZonesDataProvider;
use In2code\Femanager\Domain\Repository\UserGroupRepository;
use In2code\Femanager\Domain\Repository\UserRepository;
use In2code\Femanager\Domain\Service\RatelimiterService;
use In2code\Femanager\Domain\Service\SendMailService;
use In2code\Femanager\Finisher\FinisherRunner;
use In2code\Femanager\Utility\LogUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class DataController extends ActionController
{
    public function __construct(
        protected UserRepository $userRepository,
        protected UserGroupRepository $userGroupRepository,
        protected PersistenceManager $persistenceManager,
        protected SendMailService $sendMailService,
        protected FinisherRunner $finisherRunner,
        protected LogUtility $logUtility,
        protected RatelimiterService $ratelimiterService,
        protected CountryZonesDataProvider $countryZonesDataProvider)
    {
    }

    public function getStatesForCountryAction(string $country): ResponseInterface
    {
        $countryZones = $this->countryZonesDataProvider->getCountryZonesForCountryIso3($country);
        $jsonData = [];
        foreach ($countryZones as $countryZone) {
            $jsonData[$countryZone->getIsoCode()] = $countryZone->getLocalName();
        }

        return $this->jsonResponse(json_encode($jsonData, JSON_THROW_ON_ERROR));
    }
}
