<?php
declare(strict_types=1);
namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class SendParametersService
 */
class SendParametersService
{

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * Constructor
     */
    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * SendPost - Send values via curl to target
     *
     * @param User $user User properties
     * @return void
     */
    public function send(User $user)
    {
        $this->initialize($user);
        $this->contentObject->start($this->properties);
        if ($this->isTurnedOn()) {
            $curlObject = curl_init();
            curl_setopt($curlObject, CURLOPT_URL, $this->getUri());
            curl_setopt($curlObject, CURLOPT_POST, 1);
            curl_setopt($curlObject, CURLOPT_POSTFIELDS, $this->getData());
            curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
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
        return $this->contentObject->cObjGetSingle($this->configuration['data'], $this->configuration['data.']);
    }

    /**
     * Write to devlog
     *
     * @return bool
     */
    protected function log()
    {
        if (!empty($this->configuration['debug'])) {
            GeneralUtility::devLog(
                'femanager sendpost values',
                'femanager',
                0,
                [
                    'url' => $this->getUri(),
                    'data' => $this->getData(),
                    'properties' => $this->properties
                ]
            );
        }
    }

    /**
     * @return bool
     */
    protected function isTurnedOn()
    {
        return $this->contentObject->cObjGetSingle($this->configuration['_enable'], $this->configuration['_enable.'])
        === '1';
    }

    /**
     * Initialize
     *
     * @param User $user
     * @return void
     */
    protected function initialize(User $user)
    {
        $this->properties = $user->_getCleanProperties();
        $this->contentObject = $this->configurationManager->getContentObject();
    }
}
