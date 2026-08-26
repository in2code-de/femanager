<?php

declare(strict_types=1);

namespace In2code\Femanager\Tests\Unit\Domain\Service;

use In2code\Femanager\Domain\Service\ValidationSettingsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ValidationSettingsService::class)]
class ValidationSettingsServiceTest extends UnitTestCase
{
    protected const SR_FREECAP_EXTENSION_KEY = 'sr_freecap';

    protected bool $resetSingletonInstances = true;

    protected PackageManager $originalPackageManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalPackageManager = (new ReflectionProperty(
            ExtensionManagementUtility::class,
            'packageManager'
        ))->getValue();
    }

    protected function tearDown(): void
    {
        ExtensionManagementUtility::setPackageManager($this->originalPackageManager);
        parent::tearDown();
    }

    public static function isCaptchaEnabledDataProvider(): array
    {
        return [
            'enabled for the given controller and validation name' => [
                ['new' => ['validation' => ['captcha' => ['captcha' => '1']]]],
                true,
            ],
            'switched off' => [
                ['new' => ['validation' => ['captcha' => ['captcha' => '0']]]],
                false,
            ],
            'not configured' => [
                ['new' => ['validation' => []]],
                false,
            ],
            'configured for a different controller' => [
                ['edit' => ['validation' => ['captcha' => ['captcha' => '1']]]],
                false,
            ],
        ];
    }

    #[DataProvider('isCaptchaEnabledDataProvider')]
    #[Test]
    public function isCaptchaEnabledEvaluatesTheValidationSettings(
        array $typoScriptSettings,
        bool $expectedResult
    ): void {
        $this->setSrFreecapLoaded(true);
        $this->stubTypoScriptSettings($typoScriptSettings);
        $service = new ValidationSettingsService('new', 'validation');

        self::assertSame($expectedResult, $service->isCaptchaEnabled());
    }

    #[Test]
    public function isCaptchaEnabledIsFalseWithoutTheCaptchaProvidingExtension(): void
    {
        $this->setSrFreecapLoaded(false);
        $this->stubTypoScriptSettings(['new' => ['validation' => ['captcha' => ['captcha' => '1']]]]);
        $service = new ValidationSettingsService('new', 'validation');

        self::assertFalse($service->isCaptchaEnabled());
    }

    /**
     * ConfigurationUtility::getConfiguration() reads the settings through the extbase configuration manager.
     */
    protected function stubTypoScriptSettings(array $typoScriptSettings): void
    {
        $configurationManager = $this->createMock(ConfigurationManagerInterface::class);
        $configurationManager->method('getConfiguration')->willReturn($typoScriptSettings);
        GeneralUtility::setSingletonInstance(ConfigurationManagerInterface::class, $configurationManager);
    }

    protected function setSrFreecapLoaded(bool $isLoaded): void
    {
        $packageManager = $this->createMock(PackageManager::class);
        $packageManager->method('isPackageActive')->willReturnCallback(
            static fn (string $packageKey): bool => $packageKey === self::SR_FREECAP_EXTENSION_KEY && $isLoaded
        );
        ExtensionManagementUtility::setPackageManager($packageManager);
    }
}
