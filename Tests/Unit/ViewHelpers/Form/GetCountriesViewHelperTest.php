<?php

namespace In2code\Femanager\Tests\Unit\ViewHelpers\Form;

use In2code\Femanager\ViewHelpers\Form\GetCountriesViewHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class GetCountriesTest
 * @coversDefaultClass \In2code\Femanager\ViewHelpers\Form\GetCountriesViewHelper
 */
class GetCountriesViewHelperTest extends UnitTestCase
{
    protected GetCountriesViewHelper $generalValidatorMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->generalValidatorMock = $this->getAccessibleMock(
            GetCountriesViewHelper::class,
            null
        );
    }

    public function tearDown(): void
    {
        unset($this->generalValidatorMock);
    }

    /**
     * @covers ::render
     */
    public function testRenderReturnArray()
    {
        $result = $this->generalValidatorMock->_call('render');
        self::assertArrayHasKey('DEU', $result);
        self::assertArrayHasKey('FRA', $result);
        self::assertArrayHasKey('SWZ', $result);
    }
}
