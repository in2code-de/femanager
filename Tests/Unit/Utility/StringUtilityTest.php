<?php

namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Utility\StringUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class StringUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\StringUtility
 */
class StringUtilityTest extends UnitTestCase
{
    public static function cleanStringReturnsStringDataProvider(): array
    {
        return [
            [
                'This is a test',
                'This_is_a_test',
            ],
            [
                'Heiße Liebe',
                'Hei__e_Liebe',
            ],
            [
                'this.is_a.test',
                'this.is_a.test',
            ],
            [
                '?ß#;,-&',
                '______-_',
            ],
        ];
    }

    /**
     * @dataProvider cleanStringReturnsStringDataProvider
     * @covers ::cleanString
     */
    public function testCleanStringReturnsString(string $string, string $expectedResult): void
    {
        self::assertEquals($expectedResult, StringUtility::cleanString($string));
    }

    public static function getValuesInBracketsReturnsStringDataProvider(): array
    {
        return [
            // #0
            [
                'lala(1,2,3,5)test',
                '1,2,3,5',
            ],
            // #1
            [
                '(1,2,3,5)',
                '1,2,3,5',
            ],
            // #2
            [
                'min(10)',
                '10',
            ],
        ];
    }

    /**
     * @dataProvider getValuesInBracketsReturnsStringDataProvider
     * @covers ::getValuesInBrackets
     */
    public function testGetValuesInBracketsReturnsString(string $start, string $expectedResult): void
    {
        $result = StringUtility::getValuesInBrackets($start);
        self::assertEquals($result, $expectedResult);
    }

    public static function getValuesBeforeBracketsDataProvider(): array
    {
        return [
            // #0
            [
                'lala(1,2,3,5)test',
                'lala',
            ],
            // #1
            [
                '.()',
                '.',
            ],
            // #2
            [
                'min(10)',
                'min',
            ],
        ];
    }

    /**
     * @dataProvider getValuesBeforeBracketsDataProvider
     * @covers ::getValuesBeforeBrackets
     */
    public function testGetValuesBeforeBracketsReturnsString(string $start, string $expectedResult): void
    {
        $result = StringUtility::getValuesBeforeBrackets($start);
        self::assertEquals($result, $expectedResult);
    }

    public static function startsWithReturnsStringDataProvider(): array
    {
        return [
            [
                'Finisherx',
                'Finisher',
                true,
            ],
            [
                'inisher',
                'Finisher',
                false,
            ],
            [
                'abc',
                'a',
                true,
            ],
            [
                'abc',
                'ab',
                true,
            ],
            [
                'abc',
                'abc',
                true,
            ],
        ];
    }

    /**
     * @dataProvider startsWithReturnsStringDataProvider
     * @covers ::startsWith
     */
    public function testStartsWithReturnsString(string $haystack, string $needle, bool $expectedResult): void
    {
        self::assertSame($expectedResult, StringUtility::startsWith($haystack, $needle));
    }

    public static function endsWithReturnsStringDataProvider(): array
    {
        return [
            [
                'xFinisher',
                'Finisher',
                true,
            ],
            [
                'inisher',
                'Finisher',
                false,
            ],
            [
                'abc',
                'c',
                true,
            ],
            [
                'abc',
                'bc',
                true,
            ],
            [
                'abc',
                'abc',
                true,
            ],
        ];
    }

    /**
     * @dataProvider endsWithReturnsStringDataProvider
     * @covers ::endsWith
     */
    public function testEndsWithReturnsString(string $haystack, string $needle, bool $expectedResult): void
    {
        self::assertSame($expectedResult, StringUtility::endsWith($haystack, $needle));
    }

    public static function makeEmailArrayReturnsArrayDataProvider(): array
    {
        return [
            [
                'email1@mail.org' . PHP_EOL . 'email2@mail.org',
                [
                    'email1@mail.org' => 'femanager',
                    'email2@mail.org' => 'femanager',
                ],
            ],
            [
                'nomail.org' . PHP_EOL . 'email2@mail.org',
                [
                    'email2@mail.org' => 'femanager',
                ],
            ],
            [
                'email2@mail.org',
                [
                    'email2@mail.org' => 'femanager',
                ],
            ],
        ];
    }

    /**
     * @dataProvider makeEmailArrayReturnsArrayDataProvider
     * @covers ::makeEmailArray
     */
    public function testMakelEmailArrayReturnsArray(string $haystack, array $expectedResult): void
    {
        self::assertSame($expectedResult, StringUtility::makeEmailArray($haystack));
    }

    public static function getRandomStringAlwaysReturnsStringsOfGivenLengthDataProvider(): array
    {
        return [
            'default params' => [
                32,
                true,
                false,
            ],
            'default length lowercase' => [
                32,
                false,
                false,
            ],
            '60 length' => [
                60,
                true,
                false,
            ],
            '60 length lowercase' => [
                60,
                false,
                false,
            ],
            '60 length special characters' => [
                60,
                false,
                true,
            ],
        ];
    }

    /**
     * @dataProvider getRandomStringAlwaysReturnsStringsOfGivenLengthDataProvider
     * @covers ::getRandomString
     */
    public function testGetRandomStringAlwaysReturnsStringsOfGivenLength(
        int $length,
        bool $addUpperCase,
        bool $addSpecialCharacters
    ): void {
        for ($i = 0; $i < 100; $i++) {
            $string = StringUtility::getRandomString($length, $addUpperCase, $addSpecialCharacters);
            if ($addSpecialCharacters === false) {
                $regex = $addUpperCase ? '~[a-zA-Z0-9]{' . $length . '}~' : '~[a-z0-9]{' . $length . '}~';
            } else {
                $regex = '~.{' . $length . '}~';
            }

            self::assertSame(1, preg_match($regex, $string));
        }
    }

    /**
     * @covers ::getNumbersString
     */
    public function testGetNumbersStringReturnsStrings(): void
    {
        self::assertSame('0123456789', StringUtility::getNumbersString());
    }

    /**
     * @covers ::getCharactersString
     */
    public function testGetCharactersStringReturnsStrings(): void
    {
        self::assertSame('abcdefghijklmnopqrstuvwxyz', StringUtility::getCharactersString());
    }

    /**
     * @covers ::getUpperCharactersString
     */
    public function testGetUpperCharactersStringReturnsStrings(): void
    {
        self::assertSame('ABCDEFGHIJKLMNOPQRSTUVWXYZ', StringUtility::getUpperCharactersString());
    }

    public static function removeDoubleSlashesReturnsStringDataProvider(): array
    {
        return [
            [
                '/folder1/page.html',
                '/folder1/page.html',
            ],
            [
                '/folder1//page.html',
                '/folder1/page.html',
            ],
            [
                '/folder1///page.html',
                '/folder1/page.html',
            ],
            [
                '//folder1//folder2//',
                '/folder1/folder2/',
            ],
            [
                'index.php?id=123&param[xx]=yyy',
                'index.php?id=123&param[xx]=yyy',
            ],
            [
                'https://www.test.org/folder/page.html',
                'https://www.test.org/folder/page.html',
            ],
            [
                'https://www.test.org//folder///page.html',
                'https://www.test.org/folder/page.html',
            ],
            [
                'http://www.test.org//folder///page.html',
                'http://www.test.org/folder/page.html',
            ],
            [
                'www.test.org//folder///page.html',
                'www.test.org/folder/page.html',
            ],
        ];
    }

    /**
     * @dataProvider removeDoubleSlashesReturnsStringDataProvider
     * @covers ::removeDoubleSlashesFromUri
     */
    public function testRemoveDoubleSlashesReturnsString(string $uri, string $expectedResult): void
    {
        $newUri = StringUtility::removeDoubleSlashesFromUri($uri);
        self::assertSame($expectedResult, $newUri);
    }
}
