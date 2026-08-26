<?php

declare(strict_types=1);

namespace In2code\Femanager\Tests\Unit\Domain\Validator;

use In2code\Femanager\Domain\Repository\UserRepository;
use In2code\Femanager\Domain\Validator\CaptchaValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionProperty;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(CaptchaValidator::class)]
class CaptchaValidatorTest extends UnitTestCase
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

    /**
     * Every case must be recognized as "captcha is required", otherwise the captcha is silently skipped.
     */
    public static function captchaIsEnabledDataProvider(): array
    {
        return [
            'registration plugin reads new.validation' => [
                'tx_femanager_registration',
                '',
                ['new' => ['validation' => ['captcha' => ['captcha' => '1']]]],
            ],
            'edit plugin reads edit.validation' => [
                'tx_femanager_edit',
                '',
                ['edit' => ['validation' => ['captcha' => ['captcha' => '1']]]],
            ],
            'invitation plugin reads invitation.validation' => [
                'tx_femanager_invitation',
                '',
                ['invitation' => ['validation' => ['captcha' => ['captcha' => '1']]]],
            ],
            'invitation plugin reads invitation.validationEdit on the edit referrer' => [
                'tx_femanager_invitation',
                'edit',
                ['invitation' => ['validationEdit' => ['captcha' => ['captcha' => '1']]]],
            ],
        ];
    }

    #[DataProvider('captchaIsEnabledDataProvider')]
    #[Test]
    public function captchaIsEnabledWhenConfiguredForThePlugin(
        string $pluginNamespace,
        string $referrerActionName,
        array $typoScriptSettings
    ): void {
        $this->setSrFreecapLoaded(true);
        $validator = $this->getValidator($pluginNamespace, $referrerActionName, $typoScriptSettings);

        self::assertTrue($validator->_call('captchaEnabled'));
    }

    public static function captchaIsDisabledDataProvider(): array
    {
        return [
            'no validation settings at all' => [
                'tx_femanager_registration',
                [],
            ],
            'captcha explicitly switched off' => [
                'tx_femanager_registration',
                ['new' => ['validation' => ['captcha' => ['captcha' => '0']]]],
            ],
            'captcha configured for another controller' => [
                'tx_femanager_edit',
                ['new' => ['validation' => ['captcha' => ['captcha' => '1']]]],
            ],
            'captcha configured for another validation name' => [
                'tx_femanager_registration',
                ['new' => ['validationEdit' => ['captcha' => ['captcha' => '1']]]],
            ],
        ];
    }

    #[DataProvider('captchaIsDisabledDataProvider')]
    #[Test]
    public function captchaIsDisabledWhenNotConfiguredForThePlugin(
        string $pluginNamespace,
        array $typoScriptSettings
    ): void {
        $this->setSrFreecapLoaded(true);
        $validator = $this->getValidator($pluginNamespace, '', $typoScriptSettings);

        self::assertFalse($validator->_call('captchaEnabled'));
    }

    #[Test]
    public function captchaIsDisabledWhenSrFreecapIsNotLoaded(): void
    {
        $this->setSrFreecapLoaded(false);
        $validator = $this->getValidator(
            'tx_femanager_registration',
            '',
            ['new' => ['validation' => ['captcha' => ['captcha' => '1']]]]
        );

        self::assertFalse($validator->_call('captchaEnabled'));
    }

    protected function getValidator(
        string $pluginNamespace,
        string $referrerActionName,
        array $typoScriptSettings
    ): CaptchaValidator&AccessibleObjectInterface&MockObject {
        $this->stubTypoScriptSettings($typoScriptSettings);

        $validator = $this->getAccessibleMock(
            CaptchaValidator::class,
            null,
            [
                new UserRepository(),
                $this->createMock(ConfigurationManagerInterface::class),
                new EventDispatcher($this->createMock(ListenerProviderInterface::class)),
            ]
        );
        $validator->_set('pluginNamespace', $pluginNamespace);
        $validator->_set('referrerActionName', $referrerActionName);

        return $validator;
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
