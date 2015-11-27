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
        return array(
            array(
                'This is a test',
                'This_is_a_test',
            ),
            array(
                'Heiße Liebe',
                'Hei__e_Liebe',
            ),
            array(
                'this.is_a.test',
                'this.is_a.test',
            ),
            array(
                '?ß#;,-&',
                '______-_',
            ),
        );
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
        return array(
            // #0
            array(
                'lala(1,2,3,5)test',
                '1,2,3,5',
            ),
            // #1
            array(
                '(1,2,3,5)',
                '1,2,3,5',
            ),
            // #2
            array(
                'min(10)',
                '10',
            ),
        );
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
        return array(
            // #0
            array(
                'lala(1,2,3,5)test',
                'lala',
            ),
            // #1
            array(
                '.()',
                '.',
            ),
            // #2
            array(
                'min(10)',
                'min',
            ),
        );
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
        return array(
            array(
                'Finisherx',
                'Finisher',
                true
            ),
            array(
                'inisher',
                'Finisher',
                false
            ),
            array(
                'abc',
                'a',
                true
            ),
            array(
                'abc',
                'ab',
                true
            ),
            array(
                'abc',
                'abc',
                true
            ),
        );
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
        return array(
            array(
                'xFinisher',
                'Finisher',
                true
            ),
            array(
                'inisher',
                'Finisher',
                false
            ),
            array(
                'abc',
                'c',
                true
            ),
            array(
                'abc',
                'bc',
                true
            ),
            array(
                'abc',
                'abc',
                true
            ),
        );
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
        return array(
            array(
                'email1@mail.org' . PHP_EOL . 'email2@mail.org',
                array(
                    'email1@mail.org' => 'femanager',
                    'email2@mail.org' => 'femanager'
                )
            ),
            array(
                'nomail.org' . PHP_EOL . 'email2@mail.org',
                array(
                    'email2@mail.org' => 'femanager'
                )
            ),
            array(
                'email2@mail.org',
                array(
                    'email2@mail.org' => 'femanager'
                )
            ),
        );
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
    public function getRandomStringAlwaysReturnsStringsOfGivenLengthDateProvider()
    {
        return array(
            'default params' => array(
                32,
                true,
                false
            ),
            'default length lowercase' => array(
                32,
                false,
                false
            ),
            '60 length' => array(
                60,
                true,
                false
            ),
            '60 length lowercase' => array(
                60,
                false,
                false
            ),
        );
    }

    /**
     * getRandomStringAlwaysReturnsStringsOfGivenLength Test
     *
     * @param int $length
     * @param bool $addUpperCase
     * @param bool $addSpecialCharacters
     * @dataProvider getRandomStringAlwaysReturnsStringsOfGivenLengthDateProvider
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
}
