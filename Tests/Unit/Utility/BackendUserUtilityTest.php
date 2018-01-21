<?php
namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Utility\BackendUserUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;

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

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::isAdminAuthentication
     * @covers \In2code\Femanager\Utility\AbstractUtility::getBackendUserAuthentication
     */
    public function testIsAdminAuthentication()
    {
        $GLOBALS['BE_USER']->user['admin'] = 1;
        $this->assertTrue(BackendUserUtility::isAdminAuthentication());
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getBackendUserAuthentication
     * @covers \In2code\Femanager\Utility\AbstractUtility::getBackendUserAuthentication
     */
    public function testGetBackendUserAuthentication()
    {
        $GLOBALS['BE_USER']->user['admin'] = 1;
        $user = BackendUserUtility::getBackendUserAuthentication();
        $this->assertSame(1, $user->user['admin']);
    }
}
