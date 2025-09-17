<?php

declare(strict_types=1);

namespace In2code\Femanager\Utility;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

class TypoScriptUtility
{
    public function __construct()
    {
    }

    /**
     * Method getTypoScript
     *
     * @desc Get the full or part of typoscript of a site
     *
     * @param ?int $pid Page id for fetching typoscript (if given, search for root pid)
     * @param ?array $path Path segment of configuration array to return (i.e. array('plugin.', 'tx_felogin_login.', 'view.'))
     * @param string $configurationType The kind of configuration to fetch - must be one of the CONFIGURATION_TYPE_* constants
     *
     * @return array
     */
    public function getTypoScript(
        ?int $pid = null,
        ?array $path = null,
        string $configurationType = ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
    ): array|string {
        $request = $this->getRequest($pid);
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $configurationManager->setRequest($request);
        $configuration = $configurationManager->getConfiguration($configurationType);

        if (!empty($path)) {
            foreach ($path as $segment) {
                if (isset($configuration[$segment])) {
                    $configuration = $configuration[$segment];
                }
            }
        }

        return $configuration;
    }

    /**
     * Method getRequest
     *
     * @desc Get the ServerRequest object and if it does not exist i.e. in cli context
     * create a new, so configuration manager can load tyoposcript configuration
     *
     * @param ?int $pid Page id to get Site (if not set, page id 1 will be used)
     *
     * @return ServerRequestInterface
     */
    protected function getRequest(?int $pid = null): ?ServerRequestInterface
    {
        $pid = $pid ?? 1;

        if (!isset($GLOBALS['TYPO3_REQUEST'])) {
            try {
                $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
                $site = $siteFinder->getSiteByPageId($pid);

                $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest($site->getBase()))
                    ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
                GeneralUtility::setIndpEnv('TYPO3_REQUEST_DIR', (string)$site->getBase());
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        return $GLOBALS['TYPO3_REQUEST'];
    }
}
