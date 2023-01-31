<?php

namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Utility\BackendUserUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BackendUserUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\BackendUserUtility
 */
class BackendUserUtilityTest extends UnitTestCase
{
    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    public function setUp(): void
    {
        $GLOBALS['BE_USER'] = GeneralUtility::makeInstance(BackendUserAuthentication::class);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::isAdminAuthentication
     * @covers \In2code\Femanager\Utility\AbstractUtility::getBackendUserAuthentication
     */
    public function testIsAdminAuthentication()
    {
        $GLOBALS['BE_USER']->user['admin'] = 1;
        self::assertTrue(BackendUserUtility::isAdminAuthentication());
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getBackendUserAuthentication
     * @covers \In2code\Femanager\Utility\AbstractUtility::getBackendUserAuthentication
     */
    public function testGetBackendUserAuthentication()
    {
        $GLOBALS['BE_USER']->user['admin'] = 1;
        $user = BackendUserUtility::getBackendUserAuthentication();
        self::assertSame(1, $user->user['admin']);
    }
}
