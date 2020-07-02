<?php
namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Tests\Helper\TestingHelper;
use In2code\Femanager\Tests\Unit\Fixture\Utility\BackendUtility as BackendUtilityFixture;
use In2code\Femanager\Utility\BackendUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class BackendUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\BackendUtility
 */
class BackendUtilityTest extends UnitTestCase
{

    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    /**
     * @return void
     */
    public function setUp()
    {
        TestingHelper::setDefaultConstants();
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getPageIdentifier
     */
    public function testGetPageIdentifier()
    {
        $_GET['id'] = 123;
        $this->assertSame(123, BackendUtility::getPageIdentifier());
    }

    /**
     * @return void
     * @covers ::getPluginOrModuleString
     */
    public function testGetPluginOrModuleString()
    {
        $result = BackendUtility::getPluginOrModuleString();
        $this->assertSame('module', $result);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getCurrentParameters
     */
    public function testGetCurrentParameters()
    {
        $testParams = ['foo' => ['bar']];
        $_GET = $testParams;
        $_POST['klks'] = ['test'];
        $this->assertSame($testParams, BackendUtilityFixture::getCurrentParametersPublic());
    }
}
