<?php
namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Utility\StringUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class StringUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\StringUtility
 */
class StringUtilityTest extends UnitTestCase
{

    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    /**
     * @return array
     */
    public function cleanStringReturnsStringDataProvider()
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
     * @param string $string
     * @param string $expectedResult
     * @return void
     * @dataProvider cleanStringReturnsStringDataProvider
     * @covers ::cleanString
     */
    public function testCleanStringReturnsString($string, $expectedResult)
    {
        $this->assertEquals($expectedResult, StringUtility::cleanString($string));
    }

    /**
     * @return array
     */
    public function getValuesInBracketsReturnsStringDataProvider()
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
     * @param string $start
     * @param string $expectedResult
     * @return void
     * @dataProvider getValuesInBracketsReturnsStringDataProvider
     * @covers ::getValuesInBrackets
     */
    public function testGetValuesInBracketsReturnsString($start, $expectedResult)
    {
        $result = StringUtility::getValuesInBrackets($start);
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return array
     */
    public function getValuesBeforeBracketsDataProvider()
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
     * @param string $start
     * @param string $expectedResult
     * @return void
     * @dataProvider getValuesBeforeBracketsDataProvider
     * @covers ::getValuesBeforeBrackets
     */
    public function testGetValuesBeforeBracketsReturnsString($start, $expectedResult)
    {
        $result = StringUtility::getValuesBeforeBrackets($start);
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return array
     */
    public function startsWithReturnsStringDataProvider()
    {
        return [
            [
                'Finisherx',
                'Finisher',
                true
            ],
            [
                'inisher',
                'Finisher',
                false
            ],
            [
                'abc',
                'a',
                true
            ],
            [
                'abc',
                'ab',
                true
            ],
            [
                'abc',
                'abc',
                true
            ],
        ];
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param bool $expectedResult
     * @dataProvider startsWithReturnsStringDataProvider
     * @return void
     * @covers ::startsWith
     */
    public function testStartsWithReturnsString($haystack, $needle, $expectedResult)
    {
        $this->assertSame($expectedResult, StringUtility::startsWith($haystack, $needle));
    }

    /**
     * @return array
     */
    public function endsWithReturnsStringDataProvider()
    {
        return [
            [
                'xFinisher',
                'Finisher',
                true
            ],
            [
                'inisher',
                'Finisher',
                false
            ],
            [
                'abc',
                'c',
                true
            ],
            [
                'abc',
                'bc',
                true
            ],
            [
                'abc',
                'abc',
                true
            ],
        ];
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param bool $expectedResult
     * @dataProvider endsWithReturnsStringDataProvider
     * @return void
     * @covers ::endsWith
     */
    public function testEndsWithReturnsString($haystack, $needle, $expectedResult)
    {
        $this->assertSame($expectedResult, StringUtility::endsWith($haystack, $needle));
    }

    /**
     * @return array
     */
    public function makeEmailArrayReturnsArrayDataProvider()
    {
        return [
            [
                'email1@mail.org' . PHP_EOL . 'email2@mail.org',
                [
                    'email1@mail.org' => 'femanager',
                    'email2@mail.org' => 'femanager'
                ]
            ],
            [
                'nomail.org' . PHP_EOL . 'email2@mail.org',
                [
                    'email2@mail.org' => 'femanager'
                ]
            ],
            [
                'email2@mail.org',
                [
                    'email2@mail.org' => 'femanager'
                ]
            ],
        ];
    }

    /**
     * @param string $haystack
     * @param array $expectedResult
     * @dataProvider makeEmailArrayReturnsArrayDataProvider
     * @return void
     * @covers ::makeEmailArray
     */
    public function testMakelEmailArrayReturnsArray($haystack, $expectedResult)
    {
        $this->assertSame($expectedResult, StringUtility::makeEmailArray($haystack));
    }

    /**
     * @return array
     */
    public function getRandomStringAlwaysReturnsStringsOfGivenLengthDataProvider()
    {
        return [
            'default params' => [
                32,
                true,
                false
            ],
            'default length lowercase' => [
                32,
                false,
                false
            ],
            '60 length' => [
                60,
                true,
                false
            ],
            '60 length lowercase' => [
                60,
                false,
                false
            ],
            '60 length special characters' => [
                60,
                false,
                true
            ]
        ];
    }

    /**
     * @param int $length
     * @param bool $addUpperCase
     * @param bool $addSpecialCharacters
     * @dataProvider getRandomStringAlwaysReturnsStringsOfGivenLengthDataProvider
     * @return void
     * @covers ::getRandomString
     */
    public function testGetRandomStringAlwaysReturnsStringsOfGivenLength($length, $addUpperCase, $addSpecialCharacters)
    {
        for ($i = 0; $i < 100; $i++) {
            $string = StringUtility::getRandomString($length, $addUpperCase, $addSpecialCharacters);
            if ($addSpecialCharacters === false) {
                if ($addUpperCase) {
                    $regex = '~[a-zA-Z0-9]{' . $length . '}~';
                } else {
                    $regex = '~[a-z0-9]{' . $length . '}~';
                }
            } else {
                $regex = '~.{' . $length . '}~';
            }
            $this->assertSame(1, preg_match($regex, $string));
        }
    }

    /**
     * @return void
     * @covers ::getNumbersString
     */
    public function testGetNumbersStringReturnsStrings()
    {
        $this->assertSame('0123456789', StringUtility::getNumbersString());
    }

    /**
     * @return void
     * @covers ::getCharactersString
     */
    public function testGetCharactersStringReturnsStrings()
    {
        $this->assertSame('abcdefghijklmnopqrstuvwxyz', StringUtility::getCharactersString());
    }

    /**
     * @return void
     * @covers ::getUpperCharactersString
     */
    public function testGetUpperCharactersStringReturnsStrings()
    {
        $this->assertSame('ABCDEFGHIJKLMNOPQRSTUVWXYZ', StringUtility::getUpperCharactersString());
    }

    /**
     * @return array
     */
    public function removeDoubleSlashesReturnsStringDataProvider()
    {
        return [
            [
                '/folder1/page.html',
                '/folder1/page.html'
            ],
            [
                '/folder1//page.html',
                '/folder1/page.html'
            ],
            [
                '/folder1///page.html',
                '/folder1/page.html'
            ],
            [
                '//folder1//folder2//',
                '/folder1/folder2/'
            ],
            [
                'index.php?id=123&param[xx]=yyy',
                'index.php?id=123&param[xx]=yyy'
            ],
            [
                'https://www.test.org/folder/page.html',
                'https://www.test.org/folder/page.html'
            ],
            [
                'https://www.test.org//folder///page.html',
                'https://www.test.org/folder/page.html'
            ],
            [
                'http://www.test.org//folder///page.html',
                'http://www.test.org/folder/page.html'
            ],
            [
                'www.test.org//folder///page.html',
                'www.test.org/folder/page.html'
            ]
        ];
    }

    /**
     * @param string $uri
     * @param string $expectedResult
     * @dataProvider removeDoubleSlashesReturnsStringDataProvider
     * @return void
     * @covers ::removeDoubleSlashesFromUri
     */
    public function testRemoveDoubleSlashesReturnsString($uri, $expectedResult)
    {
        $newUri = StringUtility::removeDoubleSlashesFromUri($uri);
        $this->assertSame($expectedResult, $newUri);
    }
}
