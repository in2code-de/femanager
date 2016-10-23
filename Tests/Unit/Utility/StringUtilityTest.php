<?php
namespace In2code\Femanager\Tests\Utility;

use In2code\Femanager\Utility\StringUtility;
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
 * Class StringUtilityTest
 * @package In2code\Femanager\Tests\Utility
 */
class StringUtilityTest extends UnitTestCase
{

    /**
     * Dataprovider for cleanString()
     *
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
     * Test for cleanString()
     *
     * @param string $string
     * @param string $expectedResult
     * @return void
     * @dataProvider cleanStringReturnsStringDataProvider
     * @test
     */
    public function cleanStringReturnsString($string, $expectedResult)
    {
        $this->assertEquals($expectedResult, StringUtility::cleanString($string));
    }

    /**
     * Dataprovider for getValuesInBrackets()
     *
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
     * Test for getValuesInBrackets()
     *
     * @param string $start
     * @param string $expectedResult
     * @return void
     * @dataProvider getValuesInBracketsReturnsStringDataProvider
     * @test
     */
    public function getValuesInBracketsReturnsString($start, $expectedResult)
    {
        $result = StringUtility::getValuesInBrackets($start);
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * Dataprovider for getValuesBeforeBrackets()
     *
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
     * Test for getValuesBeforeBrackets()
     *
     * @param string $start
     * @param string $expectedResult
     * @return void
     * @dataProvider getValuesBeforeBracketsDataProvider
     * @test
     */
    public function getValuesBeforeBracketsReturnsString($start, $expectedResult)
    {
        $result = StringUtility::getValuesBeforeBrackets($start);
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * Data Provider for startsWithReturnsString()
     *
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
     * startsWith Test
     *
     * @param string $haystack
     * @param string $needle
     * @param bool $expectedResult
     * @dataProvider startsWithReturnsStringDataProvider
     * @return void
     * @test
     */
    public function startsWithReturnsString($haystack, $needle, $expectedResult)
    {
        $this->assertSame($expectedResult, StringUtility::startsWith($haystack, $needle));
    }

    /**
     * Data Provider for endsWithReturnsString()
     *
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
     * endsWith Test
     *
     * @param string $haystack
     * @param string $needle
     * @param bool $expectedResult
     * @dataProvider endsWithReturnsStringDataProvider
     * @return void
     * @test
     */
    public function endsWithReturnsString($haystack, $needle, $expectedResult)
    {
        $this->assertSame($expectedResult, StringUtility::endsWith($haystack, $needle));
    }

    /**
     * Data Provider for emakeEmailArrayReturnsArray()
     *
     * @return array
     */
    public function emakeEmailArrayReturnsArrayDataProvider()
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
     * emakeEmailArray Test
     *
     * @param string $haystack
     * @param array $expectedResult
     * @dataProvider emakeEmailArrayReturnsArrayDataProvider
     * @return void
     * @test
     */
    public function emakeEmailArrayReturnsArray($haystack, $expectedResult)
    {
        $this->assertSame($expectedResult, StringUtility::makeEmailArray($haystack));
    }

    /**
     * Data Provider for getRandomStringAlwaysReturnsStringsOfGivenLength
     *
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
        ];
    }

    /**
     * getRandomStringAlwaysReturnsStringsOfGivenLength Test
     *
     * @param int $length
     * @param bool $addUpperCase
     * @param bool $addSpecialCharacters
     * @dataProvider getRandomStringAlwaysReturnsStringsOfGivenLengthDataProvider
     * @return void
     * @test
     */
    public function getRandomStringAlwaysReturnsStringsOfGivenLength($length, $addUpperCase, $addSpecialCharacters)
    {
        for ($i = 0; $i < 100; $i++) {
            $string = StringUtility::getRandomString($length, $addUpperCase, $addSpecialCharacters);
            if ($addUpperCase) {
                $regex = '~[a-zA-Z0-9]{' . $length . '}~';
            } else {
                $regex = '~[a-z0-9]{' . $length . '}~';
            }
            $this->assertSame(1, preg_match($regex, $string));
        }
    }

    /**
     * getNumbersString Test
     *
     * @return void
     * @test
     */
    public function getNumbersStringReturnsStrings()
    {
        $this->assertSame('0123456789', StringUtility::getNumbersString());
    }

    /**
     * getCharactersString Test
     *
     * @return void
     * @test
     */
    public function getCharactersStringReturnsStrings()
    {
        $this->assertSame('abcdefghijklmnopqrstuvwxyz', StringUtility::getCharactersString());
    }

    /**
     * getUpperCharactersString Test
     *
     * @return void
     * @test
     */
    public function getUpperCharactersStringReturnsStrings()
    {
        $this->assertSame('ABCDEFGHIJKLMNOPQRSTUVWXYZ', StringUtility::getUpperCharactersString());
    }

    /**
     * Data Provider for removeDoubleSlashesReturnsString
     *
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
     * removeDoubleSlashes Test
     *
     * @param string $uri
     * @param string $expectedResult
     * @dataProvider removeDoubleSlashesReturnsStringDataProvider
     * @return void
     * @test
     */
    public function removeDoubleSlashesReturnsString($uri, $expectedResult)
    {
        $newUri = StringUtility::removeDoubleSlashesFromUri($uri);
        $this->assertSame($expectedResult, $newUri);
    }
}
