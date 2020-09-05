<?php
namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Utility\ConfigurationUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class ConfigurationUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\ConfigurationUtility
 */
class ConfigurationUtilityTest extends UnitTestCase
{

    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::isDisableModuleActive
     * @covers \In2code\Femanager\Utility\AbstractUtility::getExtensionConfiguration
     */
    public function testIsDisableModuleActive()
    {
        $configuration = [
            'disableModule' => '1'
        ];
        // @extensionScannerIgnoreLine
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['femanager'] = $configuration;
        $this->assertTrue(ConfigurationUtility::isDisableModuleActive());
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::isConfirmationModuleActive
     * @covers \In2code\Femanager\Utility\AbstractUtility::getExtensionConfiguration
     */
    public function testIsConfirmationModuleActive()
    {
        $configuration = [
            'enableConfirmationModule' => '1'
        ];
        // @extensionScannerIgnoreLine
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['femanager'] = $configuration;
        $this->assertTrue(ConfigurationUtility::isConfirmationModuleActive());
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::isDisableLogActive
     * @covers \In2code\Femanager\Utility\AbstractUtility::getExtensionConfiguration
     */
    public function testIsDisableLogActive()
    {
        $configuration = [
            'disableLog' => '1'
        ];
        // @extensionScannerIgnoreLine
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['femanager'] = $configuration;
        $this->assertTrue(ConfigurationUtility::isDisableLogActive());
    }
}
