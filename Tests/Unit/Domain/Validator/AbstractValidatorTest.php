<?php

namespace In2code\Femanager\Tests\Unit\Domain\Validator;

use In2code\Femanager\Tests\Unit\Fixture\Domain\Validator\AbstractValidator;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class AbstractValidatorTest
 * @coversDefaultClass \In2code\Femanager\Domain\Validator\AbstractValidator
 */
class AbstractValidatorTest extends UnitTestCase
{
    /**
     * @var \In2code\Femanager\Domain\Validator\AbstractValidator
     */
    protected $generalValidatorMock;

    /**
     * Make object available
     */
    public function setUp(): void
    {
        $this->generalValidatorMock = $this->getAccessibleMock(AbstractValidator::class, ['dummy']);
    }

    /**
     * Remove object
     */
    public function tearDown(): void
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
                true
            ],
            [
                0,
                true
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
     * @param string $value
     * @param string $expectedResult
     * @dataProvider validateRequiredReturnsBoolDataProvider
     * @covers ::validateRequired
     */
    public function testValidateRequiredReturnsBool($value, $expectedResult)
    {
        self::assertSame($expectedResult, $this->generalValidatorMock->_callRef('validateRequired', $value));
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
     * @param string $value
     * @param string $expectedResult
     * @dataProvider validateEmailReturnsBoolDataProvider
     * @covers ::validateEmail
     */
    public function testValidateEmailReturnsBool($value, $expectedResult)
    {
        self::assertSame($expectedResult, $this->generalValidatorMock->_callRef('validateEmail', $value));
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
     * @param string $value
     * @param int $allowedLength
     * @param string $expectedResult
     * @dataProvider validateMinReturnsBoolDataProvider
     * @covers ::validateMin
     */
    public function testValidateMinReturnsBool($value, $allowedLength, $expectedResult)
    {
        self::assertSame(
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
     * @param string $value
     * @param int $allowedLength
     * @param string $expectedResult
     * @dataProvider validateMaxReturnsBoolDataProvider
     * @covers ::validateMax
     */
    public function testValidateMaxReturnsBool($value, $allowedLength, $expectedResult)
    {
        self::assertSame(
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
                (PHP_MAJOR_VERSION >= 8) ? true : false
            ]
        ];
    }

    /**
     * @param string $value
     * @param bool $expectedResult
     * @dataProvider validateIntReturnsBoolDataProvider
     * @covers ::validateInt
     */
    public function testValidateIntReturnsBool($value, $expectedResult)
    {
        self::assertSame($expectedResult, $this->generalValidatorMock->_callRef('validateInt', $value));
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
     * Dataprovider for validateUnicodeLettersReturnsBool()
     *
     * @return array
     */
    public function validateUnicodeLettersReturnsBoolDataProvider()
    {
        return [
            [
                'abafdbadsf',
                true
            ],
            [
                'aeÈ-',
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
                'abäÜÄ',
                true
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
     * @param string $value
     * @param bool $expectedResult
     * @dataProvider validateLettersReturnsBoolDataProvider
     * @covers ::validateUnicodeLetters
     */
    public function testValidateLettersReturnsBool($value, $expectedResult)
    {
        self::assertSame($expectedResult, $this->generalValidatorMock->_callRef('validateLetters', $value));
    }

    /**
     * @param string $value
     * @param bool $expectedResult
     * @dataProvider validateUnicodeLettersReturnsBoolDataProvider
     * @covers ::validateLetters
     */
    public function testUnicodeValidateLettersReturnsBool($value, $expectedResult)
    {
        self::assertSame($expectedResult, $this->generalValidatorMock->_callRef('validateUnicodeLetters', $value));
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
            [
                'in2code',
                'uppercase',
                false
            ],
            [
                'In2code',
                'uppercase',
                true
            ],
            [
                'in2Code',
                'uppercase',
                true
            ],
            [
                'in2codE',
                'uppercase',
                true
            ],
            [
                'In2code',
                'number,uppercase',
                true
            ],
            [
                'I n2code',
                'number,uppercase,space',
                true
            ],
        ];
    }

    /**
     * @param string $value
     * @param string $configuration
     * @param string $expectedResult
     * @dataProvider validateMustIncludeReturnsBoolDataProvider
     * @covers ::validateMustInclude
     */
    public function testValidateMustIncludeReturnsBool($value, $configuration, $expectedResult)
    {
        self::assertSame(
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
            [
                'in2code',
                'uppercase',
                true
            ],
            [
                'In2code',
                'uppercase',
                false
            ],
            [
                'in2codE',
                'uppercase',
                false
            ],
            [
                'in2Code',
                'uppercase',
                false
            ],
        ];
    }

    /**
     * @param string $value
     * @param string $configuration
     * @param string $expectedResult
     * @dataProvider validateMustNotIncludeReturnsBoolDataProvider
     * @covers ::validateMustNotInclude
     */
    public function testValidateMustNotIncludeReturnsBool($value, $configuration, $expectedResult)
    {
        self::assertSame(
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
                '1,2',
                '1,2,3',
                true
            ],
            [
                '1,2',
                '3,2,1',
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
            [
                '1,2,3',
                '1,2',
                false
            ],
            [
                '1,2,3',
                '2,1',
                false
            ]
        ];
    }

    /**
     * @param string $value
     * @param string $configuration
     * @param string $expectedResult
     * @dataProvider validateInListReturnsBoolDataProvider
     * @covers ::validateInList
     */
    public function testValidateInListReturnsBool($value, $configuration, $expectedResult)
    {
        self::assertSame(
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
     * @param string $value
     * @param string $value2
     * @param string $result
     * @dataProvider validateSameAsReturnsBoolDateProvider
     * @covers ::validateSameAs
     */
    public function testValidateSameAsReturnsBool($value, $value2, $result)
    {
        $test = $this->generalValidatorMock->_callRef('validateSameAs', $value, $value2);
        self::assertSame($result, $test);
    }
}
