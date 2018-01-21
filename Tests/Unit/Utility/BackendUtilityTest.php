<?php
namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Tests\Helper\TestingHelper;
use In2code\Femanager\Tests\Unit\Fixture\Utility\BackendUtility as BackendUtilityFixture;
use In2code\Femanager\Utility\BackendUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;

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
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getBackendEditUri
     * @covers ::getCurrentParameters
     */
    public function testGetBackendEditUri()
    {
        $_GET['M'] = '';
        $result = '/typo3/index.php?M=record_edit&moduleToken=dummyToken&edit%5Btt_content%5D%5B123%5D=edit' .
            '&returnUrl=%2Ftypo3%2Findex.php%3FM%3D%26moduleToken%3DdummyToken';
        $this->assertSame($result, BackendUtility::getBackendEditUri('tt_content', 123));
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getBackendNewUri
     * @covers ::getCurrentParameters
     */
    public function testGetBackendNewUri()
    {
        $_GET['M'] = '';
        $result = '/typo3/index.php?M=record_edit&moduleToken=dummyToken&edit%5Btt_content%5D%5B123%5D=new';
        $this->assertSame($result, BackendUtility::getBackendNewUri('tt_content', 123, false));
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
