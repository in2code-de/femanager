<?php
namespace In2code\Femanager\Tests;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Alex Kellner <alexander.kellner@in2code.de>, in2code
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class AbstractValidatorTest
 * @package In2code\Femanager\Tests
 */
class AbstractValidatorTest extends UnitTestCase
{

    /**
     * @var \In2code\Femanager\Domain\Validator\AbstractValidator
     */
    protected $generalValidatorMock;

    /**
     * Make object available
     * @return void
     */
    public function setUp()
    {
        $this->generalValidatorMock = $this->getAccessibleMock(
            '\In2code\Femanager\Domain\Validator\AbstractValidator',
            array('dummy')
        );
    }

    /**
     * Remove object
     * @return void
     */
    public function tearDown()
    {
        unset($this->generalValidatorMock);
    }

    /**
     * Dataprovider for validateRequiredReturnsBool()
     *
     * @return array
     */
    public function validateRequiredReturnsBoolDataProvider()
    {
        return array(
            array(
                'in2code.de',
                true
            ),
            array(
                '.',
                true
            ),
            array(
                1234,
                true
            ),
            array(
                1234.56,
                true
            ),
            array(
                '',
                false
            ),
            array(
                array(),
                false
            ),
            array(
                '0',
                false
            ),
            array(
                0,
                false
            ),
            array(
                null,
                false
            ),
            array(
                false,
                false
            )
        );
    }

    /**
     * Test vor validateRequired()
     *
     * @param string $value
     * @param string $expectedResult
     * @return void
     * @dataProvider validateRequiredReturnsBoolDataProvider
     * @test
     */
    public function validateRequiredReturnsBool($value, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->generalValidatorMock->_callRef('validateRequired', $value));
    }

    /**
     * Dataprovider for validateEmailReturnsBool()
     *
     * @return array
     */
    public function validateEmailReturnsBoolDataProvider()
    {
        return array(
            array(
                'in2code.de',
                false
            ),
            array(
                '',
                false
            ),
            array(
                'alex@in2code.de',
                true
            ),
            array(
                'alex@in2code.',
                false
            ),
            array(
                'www.in2code.de',
                false
            ),
            array(
                'test@www.in2code.de',
                true
            ),
            array(
                'alex@test.test.in2code.de',
                true
            ),
        );
    }

    /**
     * Test for validateEmail()
     *
     * @param string $value
     * @param string $expectedResult
     * @return void
     * @dataProvider validateEmailReturnsBoolDataProvider
     * @test
     */
    public function validateEmailReturnsBool($value, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->generalValidatorMock->_callRef('validateEmail', $value));
    }

    /**
     * Dataprovider for validateMinReturnsBool()
     *
     * @return array
     */
    public function validateMinReturnsBoolDataProvider()
    {
        return array(
            array(
                'in2code.de',
                10,
                true
            ),
            array(
                'in2code.d',
                10,
                false
            ),
            array(
                'i',
                1,
                true
            ),
            array(
                'i',
                2,
                false
            ),
            array(
                ' i ',
                2,
                true
            )
        );
    }

    /**
     * Test vor validateMin()
     *
     * @param string $value
     * @param int $allowedLength
     * @param string $expectedResult
     * @return void
     * @dataProvider validateMinReturnsBoolDataProvider
     * @test
     */
    public function validateMinReturnsBool($value, $allowedLength, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->generalValidatorMock->_callRef('validateMin', $value, $allowedLength)
        );
    }

    /**
     * Dataprovider for validateMaxReturnsBool()
     *
     * @return array
     */
    public function validateMaxReturnsBoolDataProvider()
    {
        return array(
            array(
                'in2code.de',
                10,
                true
            ),
            array(
                'in2code.de.',
                10,
                false
            ),
            array(
                'i',
                1,
                true
            ),
            array(
                'i',
                2,
                true
            ),
            array(
                ' i ',
                2,
                false
            ),
            array(
                'i',
                0,
                false
            )
        );
    }

    /**
     * Test vor validateMax()
     *
     * @param string $value
     * @param int $allowedLength
     * @param string $expectedResult
     * @return void
     * @dataProvider validateMaxReturnsBoolDataProvider
     * @test
     */
    public function validateMaxReturnsBool($value, $allowedLength, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->generalValidatorMock->_callRef('validateMax', $value, $allowedLength)
        );
    }

    /**
     * Dataprovider for validateIntReturnsBool()
     *
     * @return array
     */
    public function validateIntReturnsBoolDataProvider()
    {
        return array(
            array(
                '123',
                true
            ),
            array(
                '1235135',
                true
            ),
            array(
                '123a23',
                false
            ),
            array(
                '123 23',
                false
            ),
            array(
                '12323,',
                false
            ),
            array(
                '12323²',
                false
            ),
            array(
                '3 ',
                false
            )
        );
    }

    /**
     * Test vor validateInt()
     *
     * @param string $value
     * @param bool $expectedResult
     * @return void
     * @dataProvider validateIntReturnsBoolDataProvider
     * @test
     */
    public function validateIntReturnsBool($value, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->generalValidatorMock->_callRef('validateInt', $value));
    }

    /**
     * Dataprovider for validateLettersReturnsBool()
     *
     * @return array
     */
    public function validateLettersReturnsBoolDataProvider()
    {
        return array(
            array(
                'abafdbadsf',
                true
            ),
            array(
                'a_-b',
                true
            ),
            array(
                'abafd3adsf',
                false
            ),
            array(
                'abä',
                false
            ),
            array(
                'ab:',
                false
            ),
            array(
                'ab cd',
                false
            )
        );
    }

    /**
     * Test vor validateLetters()
     *
     * @param string $value
     * @param bool $expectedResult
     * @return void
     * @dataProvider validateLettersReturnsBoolDataProvider
     * @test
     */
    public function validateLettersReturnsBool($value, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->generalValidatorMock->_callRef('validateLetters', $value));
    }

    /**
     * Dataprovider for validateMustIncludeReturnsBool()
     *
     * @return array
     */
    public function validateMustIncludeReturnsBoolDataProvider()
    {
        return array(
            array(
                'in2code.de',
                'number,letter,special',
                true
            ),
            array(
                'in2code.de ',
                'number,letter,special,space',
                true
            ),
            array(
                'in2code.de',
                'number,  special',
                true
            ),
            array(
                'in2code.de',
                '   special  ,   letter ',
                true
            ),
            array(
                'in2code',
                'number,letter',
                true
            ),
            array(
                'in2code',
                'special,letter',
                false
            ),
            array(
                'in2code#',
                'special',
                true
            ),
            array(
                'in2co de',
                'special',
                true
            ),
            array(
                'in2code',
                'number',
                true
            ),
            array(
                'incode.',
                'number,letter',
                false
            ),
            array(
                'in2 code',
                'number,letter',
                true
            ),
            array(
                'in code',
                'letter',
                true
            ),
            array(
                '1 2',
                'number',
                true
            ),
            array(
                '2',
                'number',
                true
            ),
            array(
                '1 2',
                'space',
                true
            ),
            array(
                '132',
                'space',
                false
            ),
            array(
                'a;#/%äß´^á 3',
                'space',
                true
            ),
            array(
                'a;#/%äß´^á 3',
                'letter,number,special,space',
                true
            ),
            array(
                'a;#/%äß´^á 3',
                'special,space',
                true
            ),
        );
    }

    /**
     * Test for validateMustInclude()
     *
     * @param string $value
     * @param string $configuration
     * @param string $expectedResult
     * @return void
     * @dataProvider validateMustIncludeReturnsBoolDataProvider
     * @test
     */
    public function validateMustIncludeReturnsBool($value, $configuration, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->generalValidatorMock->_callRef('validateMustInclude', $value, $configuration)
        );
    }

    /**
     * Dataprovider for validateMustNotIncludeReturnsBool()
     *
     * @return array
     */
    public function validateMustNotIncludeReturnsBoolDataProvider()
    {
        return array(
            array(
                'in2code.de',
                'number,letter,special',
                false
            ),
            array(
                'in2code.de ',
                'number,letter,special,space',
                false
            ),
            array(
                'in2code.de',
                'number,  special',
                false
            ),
            array(
                'in2code.de',
                '   special  ,   letter ',
                false
            ),
            array(
                'in2code',
                'number,letter',
                false
            ),
            array(
                'in2code',
                'special,space',
                true
            ),
            array(
                'in2code#',
                'special',
                false
            ),
            array(
                'in2co3de',
                'special',
                true
            ),
            array(
                'in2code',
                'number',
                false
            ),
            array(
                'incode.',
                'number,letter',
                false
            ),
            array(
                'in2 code',
                'number,letter',
                false
            ),
            array(
                'in code',
                'letter',
                false
            ),
            array(
                '1 2',
                'number',
                false
            ),
            array(
                '2',
                'number',
                false
            ),
            array(
                '1 2',
                'space',
                false
            ),
            array(
                '132',
                'space',
                true
            ),
            array(
                'a;#/%äß´^á 3',
                'space',
                false
            ),
            array(
                'a;#/%äß´^á 3',
                'letter,number,special,space',
                false
            ),
            array(
                'a;#/%äß´^á 3',
                'special,space',
                false
            ),
        );
    }

    /**
     * Test for validateMustNotInclude()
     *
     * @param string $value
     * @param string $configuration
     * @param string $expectedResult
     * @return void
     * @dataProvider validateMustNotIncludeReturnsBoolDataProvider
     * @test
     */
    public function validateMustNotIncludeReturnsBool($value, $configuration, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->generalValidatorMock->_callRef('validateMustNotInclude', $value, $configuration)
        );
    }

    /**
     * Dataprovider for validateInListReturnsBool()
     *
     * @return array
     */
    public function validateInListReturnsBoolDataProvider()
    {
        return array(
            array(
                '2',
                '1,2,5,8',
                true
            ),
            array(
                '2',
                '1,1,2',
                true
            ),
            array(
                '1',
                '1,3,2',
                true
            ),
            array(
                '1',
                '1,3,2',
                true
            ),
            array(
                '1',
                1,
                true
            ),
            array(
                1,
                '1,2',
                true
            ),
            array(
                'a',
                'a',
                true
            ),
            array(
                '23',
                '1,234,3',
                false
            ),
            array(
                'a',
                'ab',
                false
            ),
            array(
                'a',
                'ba',
                false
            ),
            array(
                'a',
                'bac',
                false
            ),
        );
    }

    /**
     * Test vor validateInList()
     *
     * @param string $value
     * @param string $configuration
     * @param string $expectedResult
     * @return void
     * @dataProvider validateInListReturnsBoolDataProvider
     * @test
     */
    public function validateInListReturnsBool($value, $configuration, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->generalValidatorMock->_callRef('validateInList', $value, $configuration)
        );
    }

    /**
     * Dataprovider for validateSameAsReturnsBool()
     *
     * @return array
     */
    public function validateSameAsReturnsBoolDateProvider()
    {
        return array(
            array(
                'abcd',
                'abcd',
                true
            ),
            array(
                'a',
                'b',
                false
            ),
            array(
                'a',
                '',
                false
            ),
            array(
                '',
                '',
                true
            ),
            array(
                0,
                '0',
                false
            ),
            array(
                1,
                '1',
                false
            ),
        );
    }

    /**
     * Test for validateSameAs()
     *
     * @param string $value
     * @param string $value2
     * @param string $result
     * @return void
     * @dataProvider validateSameAsReturnsBoolDateProvider
     * @test
     */
    public function validateSameAsReturnsBool($value, $value2, $result)
    {
        $test = $this->generalValidatorMock->_callRef('validateSameAs', $value, $value2);
        $this->assertSame($result, $test);
    }
}
