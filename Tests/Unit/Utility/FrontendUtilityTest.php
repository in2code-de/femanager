<?php
namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Tests\Helper\TestingHelper;
use In2code\Femanager\Utility\FrontendUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class FrontendUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\FrontendUtility
 */
class FrontendUtilityTest extends UnitTestCase
{

    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    public function setUp()
    {
        TestingHelper::setDefaultConstants();
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::forceValues
     */
    public function testForceValues()
    {
        $user = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\In2code\Femanager\Domain\Model\User::class);
        
        $settings = [];
        $settings['usergroup'] = 'TEXT';
        $settings['usergroup.'] = ['value' => '1,2,3'];
        $settings['gender'] = 'TEXT';
        $settings['gender.'] = ['value' => '2'];
        $settings['first_name'] = 'TEXT';
        $settings['first_name.'] = ['value' => 'Kaspar'];
        $settings['tx_extbase_type'] = 'TEXT';
        $settings['tx_extbase_type.'] = ['value' => 'Tx_Extbase_Domain_Model_FrontendUser'];
    
        FrontendUtility::forceValues($user, $settings);
        
        $this->assertSame(2, $user->getGender());
        $this->assertSame('1,2,3', $user->getUsergroups());
        $this->assertSame('Kaspar', $user->getFirstName());
        $this->assertSame('Tx_Extbase_Domain_Model_FrontendUser', $user->getTxExtbaseType());
    }
    
    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getControllerName
     */
    public function testGetControllerName()
    {
        $_POST['tx_femanager_pi1']['controller'] = 'foo';
        $this->assertSame('foo', FrontendUtility::getControllerName());
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getActionName
     */
    public function testGetActionName()
    {
        $_POST['tx_femanager_pi1']['action'] = 'bar';
        $this->assertSame('bar', FrontendUtility::getActionName());
    }
}
