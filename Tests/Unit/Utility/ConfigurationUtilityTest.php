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
        self::assertTrue(ConfigurationUtility::isDisableModuleActive());
    }

    /**
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
        self::assertTrue(ConfigurationUtility::isConfirmationModuleActive());
    }

    /**
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
        self::assertTrue(ConfigurationUtility::isDisableLogActive());
    }
}
