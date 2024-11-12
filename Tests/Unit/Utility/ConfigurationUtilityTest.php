<?php

namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Utility\ConfigurationUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class ConfigurationUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\ConfigurationUtility
 */
class ConfigurationUtilityTest extends UnitTestCase
{
    protected array $testFilesToDelete = [];

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::isDisableModuleActive
     * @covers \In2code\Femanager\Utility\AbstractUtility::getExtensionConfiguration
     */
    public function testIsDisableModuleActive(): void
    {
        $configuration = [
            'disableModule' => '1',
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
    public function testIsConfirmationModuleActive(): void
    {
        $configuration = [
            'enableConfirmationModule' => '1',
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
    public function testIsDisableLogActive(): void
    {
        $configuration = [
            'disableLog' => '1',
        ];
        // @extensionScannerIgnoreLine
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['femanager'] = $configuration;
        self::assertTrue(ConfigurationUtility::isDisableLogActive());
    }
}
