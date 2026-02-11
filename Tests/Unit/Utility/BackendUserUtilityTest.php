<?php

namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Utility\BackendUserUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class BackendUserUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\BackendUserUtility
 */
class BackendUserUtilityTest extends UnitTestCase
{
    protected array $testFilesToDelete = [];

    public function setUp(): void
    {
        parent::setUp();
        $GLOBALS['BE_USER'] = GeneralUtility::makeInstance(BackendUserAuthentication::class);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::isAdmin
     * @covers \In2code\Femanager\Utility\AbstractUtility::getBackendUserAuthentication
     */
    public function testIsAdmin(): void
    {
        $this->resetSingletonInstances = true;
        $GLOBALS['BE_USER']->user['admin'] = 1;
        self::assertTrue(BackendUserUtility::isAdmin());
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getBackendUserAuthentication
     * @covers \In2code\Femanager\Utility\AbstractUtility::getBackendUserAuthentication
     */
    public function testGetBackendUserAuthentication(): void
    {
        $this->resetSingletonInstances = true;
        $GLOBALS['BE_USER']->user['admin'] = 1;

        $method = new \ReflectionMethod(BackendUserUtility::class, 'getBackendUserAuthentication');
        $user = $method->invoke(null);
        self::assertSame(1, $user->user['admin']);
    }
}
