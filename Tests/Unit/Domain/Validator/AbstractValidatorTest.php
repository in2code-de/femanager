<?php

namespace In2code\Femanager\Tests\Unit\Domain\Validator;

use In2code\Femanager\Domain\Validator\AbstractValidator;
use In2code\Femanager\Tests\Unit\Fixture\Domain\Validator\AbstractValidator as AbstractValidatorFixture;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class AbstractValidatorTest
 * @coversDefaultClass AbstractValidator
 */
class AbstractValidatorTest extends UnitTestCase
{
    protected AccessibleObjectInterface|MockObject|AbstractValidatorFixture|AbstractValidator $generalValidatorMock;

    /**
     * Make object available
     */
    public function setUp(): void
    {
        $this->generalValidatorMock = $this->getAccessibleMock(
            AbstractValidatorFixture::class,
            null
        );
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
     */
    public static function validateRequiredReturnsBoolDataProvider(): array
    {
        return [
            [
                'in2code.de',
                true,
            ],
            [
                '.',
                true,
            ],
            [
                1234,
                true,
            ],
            [
                1234.56,
                true,
            ],
            [
                '',
                false,
            ],
            [
                [],
                false,
            ],
            [
                '0',
                true,
            ],
            [
                0,
                true,
            ],
            [
                null,
                false,
            ],
            [
                false,
                false,
            ],
        ];
    }

    /**
     * @dataProvider validateRequiredReturnsBoolDataProvider
     * @covers ::validateRequired
     */
    public function testValidateRequiredReturnsBool(mixed $value, bool $expectedResult)
    {
        self::assertSame($expectedResult, $this->generalValidatorMock->_call('validateRequired', $value));
    }

    /**
     * Dataprovider for validateEmailReturnsBool()
     */
    public static function validateEmailReturnsBoolDataProvider(): array
    {
        return [
            [
                'in2code.de',
                false,
            ],
            [
                '',
                false,
            ],
            [
                'alex@in2code.de',
                true,
            ],
            [
                'alex@in2code.',
                false,
            ],
            [
                'www.in2code.de',
                false,
            ],
            [
                'test@www.in2code.de',
                true,
            ],
            [
                'alex@test.test.in2code.de',
                true,
            ],
        ];
    }

    /**
     * @dataProvider validateEmailReturnsBoolDataProvider
     * @covers ::validateEmail
     */
    public function testValidateEmailReturnsBool(string $value, bool $expectedResult)
    {
        self::assertSame($expectedResult, $this->generalValidatorMock->_call('validateEmail', $value));
    }

    /**
     * Dataprovider for validateMinReturnsBool()
     */
    public static function validateMinReturnsBoolDataProvider(): array
    {
        return [
            [
                'in2code.de',
                10,
                true,
            ],
            [
                'in2code.d',
                10,
                false,
            ],
            [
                'i',
                1,
                true,
            ],
            [
                'i',
                2,
                false,
            ],
            [
                ' i ',
                2,
                true,
            ],
        ];
    }

    /**
     * @dataProvider validateMinReturnsBoolDataProvider
     * @covers ::validateMin
     */
    public function testValidateMinReturnsBool(string $value, int $allowedLength, bool $expectedResult)
    {
        self::assertSame(
            $expectedResult,
            $this->generalValidatorMock->_call('validateMin', $value, $allowedLength)
        );
    }

    /**
     * Dataprovider for validateMaxReturnsBool()
     */
    public static function validateMaxReturnsBoolDataProvider(): array
    {
        return [
            [
                'in2code.de',
                10,
                true,
            ],
            [
                'in2code.de.',
                10,
                false,
            ],
            [
                'i',
                1,
                true,
            ],
            [
                'i',
                2,
                true,
            ],
            [
                ' i ',
                2,
                false,
            ],
            [
                'i',
                0,
                false,
            ],
        ];
    }

    /**
     * @dataProvider validateMaxReturnsBoolDataProvider
     * @covers ::validateMax
     */
    public function testValidateMaxReturnsBool(string $value, int $allowedLength, bool $expectedResult)
    {
        self::assertSame(
            $expectedResult,
            $this->generalValidatorMock->_call('validateMax', $value, $allowedLength)
        );
    }

    /**
     * Dataprovider for validateIntReturnsBool()
     */
    public static function validateIntReturnsBoolDataProvider(): array
    {
        return [
            [
                '123',
                true,
            ],
            [
                '1235135',
                true,
            ],
            [
                '123a23',
                false,
            ],
            [
                '123 23',
                false,
            ],
            [
                '12323,',
                false,
            ],
            [
                '12323²',
                false,
            ],
            [
                '3 ',
                PHP_MAJOR_VERSION >= 8,
            ],
        ];
    }

    /**
     * @dataProvider validateIntReturnsBoolDataProvider
     * @covers ::validateInt
     */
    public function testValidateIntReturnsBool(string $value, bool $expectedResult)
    {
        self::assertSame($expectedResult, $this->generalValidatorMock->_call('validateInt', $value));
    }

    /**
     * Dataprovider for validateLettersReturnsBool()
     */
    public static function validateLettersReturnsBoolDataProvider(): array
    {
        return [
            [
                'abafdbadsf',
                true,
            ],
            [
                'a_-b',
                true,
            ],
            [
                'abafd3adsf',
                false,
            ],
            [
                'abä',
                false,
            ],
            [
                'ab:',
                false,
            ],
            [
                'ab cd',
                false,
            ],
        ];
    }

    /**
     * Dataprovider for validateUnicodeLettersReturnsBool()
     */
    public static function validateUnicodeLettersReturnsBoolDataProvider(): array
    {
        return [
            [
                'abafdbadsf',
                true,
            ],
            [
                'aeÈ-',
                true,
            ],
            [
                'a_-b',
                true,
            ],
            [
                'abafd3adsf',
                false,
            ],
            [
                'abäÜÄ',
                true,
            ],
            [
                'ab:',
                false,
            ],
            [
                'ab cd',
                false,
            ],
        ];
    }

    /**
     * @dataProvider validateLettersReturnsBoolDataProvider
     * @covers ::validateUnicodeLetters
     */
    public function testValidateLettersReturnsBool(string $value, bool $expectedResult)
    {
        self::assertSame($expectedResult, $this->generalValidatorMock->_call('validateLetters', $value));
    }

    /**
     * @dataProvider validateUnicodeLettersReturnsBoolDataProvider
     * @covers ::validateLetters
     */
    public function testUnicodeValidateLettersReturnsBool(string $value, bool $expectedResult)
    {
        self::assertSame($expectedResult, $this->generalValidatorMock->_call('validateUnicodeLetters', $value));
    }

    public static function validateStringReturnsBoolDataProvider(): array
    {
        return [
            [
                'in2code.de',
                'number,letter,special',
                false,
                false,
            ],
            [
                'in2code.de ',
                'number,letter,special,space',
                false,
                false,
            ],
            [
                'in2code.de',
                'number,  special',
                false,
                false,
            ],
            [
                'in2code.de',
                '   special  ,   letter ',
                false,
                false,
            ],
            [
                'in2code',
                'number,letter',
                false,
                false,
            ],
            [
                'in2code',
                'special,space',
                false,
                true,
            ],
            [
                'in2code#',
                'special',
                false,
                false,
            ],
            [
                'in2co3de',
                'special',
                false,
                true,
            ],
            [
                'in2code',
                'number',
                false,
                false,
            ],
            [
                'incode.',
                'number,letter',
                false,
                false,
            ],
            [
                'in2 code',
                'number,letter',
                false,
                false,
            ],
            [
                'in code',
                'letter',
                false,
                false,
            ],
            [
                '1 2',
                'number',
                false,
                false,
            ],
            [
                '2',
                'number',
                false,
                false,
            ],
            [
                '1 2',
                'space',
                false,
                false,
            ],
            [
                '132',
                'space',
                false,
                true,
            ],
            [
                'a;#/%äß´^á 3',
                'space',
                false,
                false,
            ],
            [
                'a;#/%äß´^á 3',
                'letter,number,special,space',
                false,
                false,
            ],
            [
                'a;#/%äß´^á 3',
                'special,space',
                false,
                false,
            ],
            [
                'in2code',
                'uppercase',
                false,
                true,
            ],
            [
                'In2code',
                'uppercase',
                false,
                false,
            ],
            [
                'in2codE',
                'uppercase',
                false,
                false,
            ],
            [
                'in2Code',
                'uppercase',
                false,
                false,
            ],
            [
                'in2code.de',
                'number,letter,special',
                true,
                true,
            ],
            [
                'in2code.de ',
                'number,letter,special,space',
                true,
                true,
            ],
            [
                'in2code.de',
                'number,  special',
                true,
                true,
            ],
            [
                'in2code.de',
                '   special  ,   letter ',
                true,
                true,
            ],
            [
                'in2code',
                'number,letter',
                true,
                true,
            ],
            [
                'in2code',
                'special,letter',
                true,
                false,
            ],
            [
                'in2code#',
                'special',
                true,
                true,
            ],
            [
                'in2co de',
                'special',
                true,
                true,
            ],
            [
                'in2code',
                'number',
                true,
                true,
            ],
            [
                'incode.',
                'number,letter',
                true,
                false,
            ],
            [
                'in2 code',
                'number,letter',
                true,
                true,
            ],
            [
                'in code',
                'letter',
                true,
                true,
            ],
            [
                '1 2',
                'number',
                true,
                true,
            ],
            [
                '2',
                'number',
                true,
                true,
            ],
            [
                '1 2',
                'space',
                true,
                true,
            ],
            [
                '132',
                'space',
                true,
                false,
            ],
            [
                'a;#/%äß´^á 3',
                'space',
                true,
                true,
            ],
            [
                'a;#/%äß´^á 3',
                'letter,number,special,space',
                true,
                true,
            ],
            [
                'a;#/%äß´^á 3',
                'special,space',
                true,
                true,
            ],
            [
                'in2code',
                'uppercase',
                true,
                false,
            ],
            [
                'In2code',
                'uppercase',
                true,
                true,
            ],
            [
                'in2Code',
                'uppercase',
                true,
                true,
            ],
            [
                'in2codE',
                'uppercase',
                true,
                true,
            ],
            [
                'In2code',
                'number,uppercase',
                true,
                true,
            ],
            [
                'I n2code',
                'number,uppercase,space',
                true,
                true,
            ],
        ];
    }

    /**
     * @dataProvider validateStringReturnsBoolDataProvider
     * @covers ::validateString
     */
    public function testValidateStringReturnsBool(
        string $value,
        string $configuration,
        bool $mustInclude,
        bool $expectedResult
    ) {
        self::assertSame(
            $expectedResult,
            $this->generalValidatorMock->_call(
                'validateString',
                $value,
                $configuration,
                $mustInclude
            )
        );
    }

    /**
     * Dataprovider for validateInListReturnsBool()
     */
    public static function validateInListReturnsBoolDataProvider(): array
    {
        return [
            [
                '2',
                '1,2,5,8',
                true,
            ],
            [
                '2',
                '1,1,2',
                true,
            ],
            [
                '1',
                '1,3,2',
                true,
            ],
            [
                '1',
                '1,3,2',
                true,
            ],
            [
                '1',
                1,
                true,
            ],
            [
                1,
                '1,2',
                true,
            ],
            [
                'a',
                'a',
                true,
            ],
            [
                '1,2',
                '1,2,3',
                true,
            ],
            [
                '1,2',
                '3,2,1',
                true,
            ],
            [
                '23',
                '1,234,3',
                false,
            ],
            [
                'a',
                'ab',
                false,
            ],
            [
                'a',
                'ba',
                false,
            ],
            [
                'a',
                'bac',
                false,
            ],
            [
                '1,2,3',
                '1,2',
                false,
            ],
            [
                '1,2,3',
                '2,1',
                false,
            ],
        ];
    }

    /**
     * @dataProvider validateInListReturnsBoolDataProvider
     * @covers ::validateInList
     */
    public function testValidateInListReturnsBool(mixed $value, mixed $configuration, bool $expectedResult)
    {
        self::assertSame(
            $expectedResult,
            $this->generalValidatorMock->_call('validateInList', $value, $configuration)
        );
    }

    /**
     * Dataprovider for validateSameAsReturnsBool()
     */
    public static function validateSameAsReturnsBoolDateProvider(): array
    {
        return [
            [
                'abcd',
                'abcd',
                true,
            ],
            [
                'a',
                'b',
                false,
            ],
            [
                'a',
                '',
                false,
            ],
            [
                '',
                '',
                true,
            ],
            [
                0,
                '0',
                false,
            ],
            [
                1,
                '1',
                false,
            ],
        ];
    }

    /**
     * @dataProvider validateSameAsReturnsBoolDateProvider
     * @covers ::validateSameAs
     */
    public function testValidateSameAsReturnsBool(mixed $value, mixed $value2, bool $expectedResult)
    {
        $test = $this->generalValidatorMock->_call('validateSameAs', $value, $value2);
        self::assertSame($expectedResult, $test);
    }
}
