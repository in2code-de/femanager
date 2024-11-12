<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class SendParametersService
 */
class SendParametersService
{
    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * Constructor
     * @param mixed[] $configuration
     */
    public function __construct(protected $configuration, protected \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager)
    {
    }

    /**
     * SendPost - Send values via curl to target
     *
     * @param User $user User properties
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function send(User $user): void
    {
        $this->initialize($user);
        $this->contentObject->start($this->properties);
        if ($this->isTurnedOn()) {
            $curlObject = curl_init();
            curl_setopt($curlObject, CURLOPT_URL, $this->getUri());
            curl_setopt($curlObject, CURLOPT_POST, 1);
            curl_setopt($curlObject, CURLOPT_POSTFIELDS, $this->getData());
            curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
            if ($GLOBALS['FE']['debug'] === 1) {
                curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
            }

            curl_exec($curlObject);
            curl_close($curlObject);
            $this->log();
        }
    }

    /**
     * Get URI for curl request
     *
     * @return string
     */
    protected function getUri()
    {
        return $this->configuration['targetUrl'];
    }

    /**
     * Get data array
     *
     * @return string
     */
    protected function getData()
    {
        return $this->contentObject->cObjGetSingle((string)$this->configuration['data'], (array)$this->configuration['data.']);
    }

    /**
     * Write to devlog
     */
    protected function log(): void
    {
        if (!empty($this->configuration['debug'])) {
            GeneralUtility::makeInstance(LogManager::class)
                ->getLogger(self::class)
                ->log(
                    LogLevel::INFO,
                    'femanager sendpost values',
                    [
                        'url' => $this->getUri(),
                        'data' => $this->getData(),
                        'properties' => $this->properties,
                    ]
                );
        }
    }

    protected function isTurnedOn(): bool
    {
        return $this->contentObject->cObjGetSingle((string)$this->configuration['_enable'], (array)$this->configuration['_enable.'])
            === '1';
    }

    /**
     * Initialize
     */
    protected function initialize(User $user)
    {
        $this->properties = $user->_getCleanProperties();
        $this->contentObject = $this->request->getAttribute('currentContentObject');
    }
}
