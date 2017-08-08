<?php
declare(strict_types=1);
namespace In2code\Femanager\Finisher;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class SendParametersFinisher
 */
class SendParametersFinisher extends AbstractFinisher implements FinisherInterface
{

    /**
     * Inject a complete new content object
     *
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     * @inject
     */
    protected $contentObject;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * TypoScript configuration part sendPost
     *
     * @var array
     */
    protected $configuration;

    /**
     * Initialize
     *
     * @return void
     */
    public function initializeFinisher()
    {
        $this->contentObject->start($this->user->_getProperties());
        $typoScript = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $this->configuration = $typoScript['plugin.']['tx_femanager.']['settings.']['new.']['sendPost.'];
    }

    /**
     * Send values via curl to a third party software
     *
     * @return void
     */
    public function sendFinisher()
    {
        if ($this->isEnabled()) {
            $curlSettings = $this->getCurlSettings();
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlSettings['url']);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $curlSettings['params']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_exec($curl);
            curl_close($curl);
            $this->writeToDevelopmentLog();
        }
    }

    /**
     * Write devlog entry
     *
     * @return void
     */
    protected function writeToDevelopmentLog()
    {
        if (!empty($this->configuration['debug'])) {
            GeneralUtility::devLog('SendPost Values', 'femanager', 0, $this->getCurlSettings());
        }
    }

    /**
     * CURL settings
     *
     * @return array
     * @return void
     */
    protected function getCurlSettings()
    {
        return [
            'url' => $this->getTargetUrl(),
            'params' => $this->getData()
        ];
    }

    /**
     * Get parameters
     *
     * @return string
     */
    protected function getData()
    {
        return $this->contentObject->cObjGetSingle($this->configuration['data'], $this->configuration['data.']);
    }

    protected function getTargetUrl()
    {
        $linkConfiguration = [
            'parameter' => $this->configuration['targetUrl'],
            'forceAbsoluteUrl' => '1',
            'returnLast' => 'url'
        ];
        return $this->contentObject->typoLink('dummy', $linkConfiguration);
    }

    /**
     * Check if sendPost is activated
     *
     * @return bool
     */
    protected function isEnabled()
    {
        return $this->contentObject->cObjGetSingle($this->configuration['_enable'], $this->configuration['_enable.'])
            === '1';
    }
}
