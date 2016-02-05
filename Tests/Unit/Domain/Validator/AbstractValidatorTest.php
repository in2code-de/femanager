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
            ['dummy']
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
        return [
            [
                'in2code.de',
                true
            ],
            [
                '.',
                true
            ],
            [
                1234,
                true
            ],
            [
                1234.56,
                true
            ],
            [
                '',
                false
            ],
            [
                [],
                false
            ],
            [
                '0',
                false
            ],
            [
                0,
                false
            ],
            [
                null,
                false
            ],
            [
                false,
                false
            ]
        ];
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
        return [
            [
                'in2code.de',
                false
            ],
            [
                '',
                false
            ],
            [
                'alex@in2code.de',
                true
            ],
            [
                'alex@in2code.',
                false
            ],
            [
                'www.in2code.de',
                false
            ],
            [
                'test@www.in2code.de',
                true
            ],
            [
                'alex@test.test.in2code.de',
                true
            ],
        ];
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
        return [
            [
                'in2code.de',
                10,
                true
            ],
            [
                'in2code.d',
                10,
                false
            ],
            [
                'i',
                1,
                true
            ],
            [
                'i',
                2,
                false
            ],
            [
                ' i ',
                2,
                true
            ]
        ];
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
        return [
            [
                'in2code.de',
                10,
                true
            ],
            [
                'in2code.de.',
                10,
                false
            ],
            [
                'i',
                1,
                true
            ],
            [
                'i',
                2,
                true
            ],
            [
                ' i ',
                2,
                false
            ],
            [
                'i',
                0,
                false
            ]
        ];
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
        return [
            [
                '123',
                true
            ],
            [
                '1235135',
                true
            ],
            [
                '123a23',
                false
            ],
            [
                '123 23',
                false
            ],
            [
                '12323,',
                false
            ],
            [
                '12323²',
                false
            ],
            [
                '3 ',
                false
            ]
        ];
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
        return [
            [
                'abafdbadsf',
                true
            ],
            [
                'a_-b',
                true
            ],
            [
                'abafd3adsf',
                false
            ],
            [
                'abä',
                false
            ],
            [
                'ab:',
                false
            ],
            [
                'ab cd',
                false
            ]
        ];
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
        return [
            [
                'in2code.de',
                'number,letter,special',
                true
            ],
            [
                'in2code.de ',
                'number,letter,special,space',
                true
            ],
            [
                'in2code.de',
                'number,  special',
                true
            ],
            [
                'in2code.de',
                '   special  ,   letter ',
                true
            ],
            [
                'in2code',
                'number,letter',
                true
            ],
            [
                'in2code',
                'special,letter',
                false
            ],
            [
                'in2code#',
                'special',
                true
            ],
            [
                'in2co de',
                'special',
                true
            ],
            [
                'in2code',
                'number',
                true
            ],
            [
                'incode.',
                'number,letter',
                false
            ],
            [
                'in2 code',
                'number,letter',
                true
            ],
            [
                'in code',
                'letter',
                true
            ],
            [
                '1 2',
                'number',
                true
            ],
            [
                '2',
                'number',
                true
            ],
            [
                '1 2',
                'space',
                true
            ],
            [
                '132',
                'space',
                false
            ],
            [
                'a;#/%äß´^á 3',
                'space',
                true
            ],
            [
                'a;#/%äß´^á 3',
                'letter,number,special,space',
                true
            ],
            [
                'a;#/%äß´^á 3',
                'special,space',
                true
            ],
        ];
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
        return [
            [
                'in2code.de',
                'number,letter,special',
                false
            ],
            [
                'in2code.de ',
                'number,letter,special,space',
                false
            ],
            [
                'in2code.de',
                'number,  special',
                false
            ],
            [
                'in2code.de',
                '   special  ,   letter ',
                false
            ],
            [
                'in2code',
                'number,letter',
                false
            ],
            [
                'in2code',
                'special,space',
                true
            ],
            [
                'in2code#',
                'special',
                false
            ],
            [
                'in2co3de',
                'special',
                true
            ],
            [
                'in2code',
                'number',
                false
            ],
            [
                'incode.',
                'number,letter',
                false
            ],
            [
                'in2 code',
                'number,letter',
                false
            ],
            [
                'in code',
                'letter',
                false
            ],
            [
                '1 2',
                'number',
                false
            ],
            [
                '2',
                'number',
                false
            ],
            [
                '1 2',
                'space',
                false
            ],
            [
                '132',
                'space',
                true
            ],
            [
                'a;#/%äß´^á 3',
                'space',
                false
            ],
            [
                'a;#/%äß´^á 3',
                'letter,number,special,space',
                false
            ],
            [
                'a;#/%äß´^á 3',
                'special,space',
                false
            ],
        ];
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
        return [
            [
                '2',
                '1,2,5,8',
                true
            ],
            [
                '2',
                '1,1,2',
                true
            ],
            [
                '1',
                '1,3,2',
                true
            ],
            [
                '1',
                '1,3,2',
                true
            ],
            [
                '1',
                1,
                true
            ],
            [
                1,
                '1,2',
                true
            ],
            [
                'a',
                'a',
                true
            ],
            [
                '23',
                '1,234,3',
                false
            ],
            [
                'a',
                'ab',
                false
            ],
            [
                'a',
                'ba',
                false
            ],
            [
                'a',
                'bac',
                false
            ],
        ];
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
        return [
            [
                'abcd',
                'abcd',
                true
            ],
            [
                'a',
                'b',
                false
            ],
            [
                'a',
                '',
                false
            ],
            [
                '',
                '',
                true
            ],
            [
                0,
                '0',
                false
            ],
            [
                1,
                '1',
                false
            ],
        ];
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
