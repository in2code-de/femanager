<?php

namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Domain\Model\User;
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

    public function setUp(): void
    {
        TestingHelper::setDefaultConstants();
    }

    /**
     * @covers ::forceValue
     */
    public function testForceValue()
    {
        $user = new User();

        $properties = [];
        $properties['gender'] = 2;
        $properties['first_name'] = 'Kaspar';
        $properties['tx_extbase_type'] = 'Tx_Extbase_Domain_Model_FrontendUser';

        foreach ($properties as $field => $value) {
            // set value
            FrontendUtility::forceValue($user, $field, $value);
        }

        self::assertSame(2, $user->getGender());
        self::assertSame('Kaspar', $user->getFirstName());
        self::assertSame('Tx_Extbase_Domain_Model_FrontendUser', $user->getTxExtbaseType());
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getControllerName
     */
    public function testGetControllerName()
    {
        $_POST['tx_femanager_pi1']['controller'] = 'foo';
        self::assertSame('foo', FrontendUtility::getControllerName());
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getActionName
     */
    public function testGetActionName()
    {
        $_POST['tx_femanager_pi1']['action'] = 'bar';
        self::assertSame('bar', FrontendUtility::getActionName());
    }
}
