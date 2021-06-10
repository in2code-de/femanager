<?php
namespace In2code\Femanager\Tests\Unit\ViewHelpers\Form;

use In2code\Femanager\ViewHelpers\Form\GetCountriesViewHelper;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class GetCountriesTest
 * @coversDefaultClass \In2code\Femanager\ViewHelpers\Form\GetCountriesViewHelper
 */
class GetCountriesViewHelperTest extends UnitTestCase
{

    /**
     * @var GetCountriesViewHelper
     */
    protected $generalValidatorMock;

    public function setUp()
    {
        $this->generalValidatorMock = $this->getAccessibleMock(GetCountriesViewHelper::class, ['dummy']);
    }

    public function tearDown()
    {
        unset($this->generalValidatorMock);
    }

    /**
     * @covers ::render
     */
    public function testRenderReturnArray()
    {
        $result = $this->generalValidatorMock->_call('render');
        $this->assertTrue(array_key_exists('DEU', $result));
        $this->assertTrue(array_key_exists('FRA', $result));
        $this->assertTrue(array_key_exists('SWZ', $result));
    }
}
