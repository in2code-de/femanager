<?php
namespace In2code\Femanager\Tests\Utility;

use In2code\Femanager\Utility\Div;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;

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
 * Class DivTest
 * @package In2code\Femanager\Tests\Utility
 */
class DivTest extends BaseTestCase
{

    /**
     * @var
     */
    protected $fixture;

    /**
     * Make object available
     *
     * @return void
     */
    public function setUp()
    {
        $this->fixture = new Div();
    }

    /**
     * Remove object
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->fixture);
    }

    /**
     * Dataprovider for checkExtension()
     *
     * @return array
     */
    public function checkExtensionReturnBoolDataProvider()
    {
        return array(
            array(
                'theImage_dot.com',
                false,
            ),
            array(
                'theImage_dot.com.jpg',
                true,
            ),
            array(
                'test.ImagetheImage_dot.com.JPEG',
                true,
            ),
            array(
                'test.png',
                true,
            ),
            array(
                'SoNenntEinRedakteurEineDätaei.PNG',
                true,
            ),
            array(
                'test.phx.bmp',
                true,
            ),
            array(
                'test.php.bmp',
                false,
            ),
            array(
                'test.phtml.bmp',
                false,
            ),
        );
    }

    /**
     * Test for checkExtension()
     *
     * @param string $givenValue
     * @param string $expectedResult
     * @return void
     * @dataProvider checkExtensionReturnBoolDataProvider
     * @test
     */
    public function checkExtensionReturnBool($givenValue, $expectedResult)
    {
        $result = Div::checkExtension($givenValue);
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * Dataprovider for isMd5()
     *
     * @return array
     */
    public function isMd5ReturnBoolDataProvider()
    {
        return array(
            // #0
            array(
                md5('aeiou'),
                true,
            ),
            // #1
            array(
                '409898rphfsdfapasdfu898weqr',
                false,
            ),
            // #2
            array(
                1238097720989832023900,
                false,
            ),
        );
    }

    /**
     * Test for isMd5()
     *
     * @param string $givenValue
     * @param string $expectedResult
     * @return void
     * @dataProvider isMd5ReturnBoolDataProvider
     * @test
     */
    public function isMd5ReturnBool($givenValue, $expectedResult)
    {
        $result = Div::isMd5($givenValue);
        $this->assertEquals($result, $expectedResult);
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
        $result = Div::getValuesInBrackets($start);
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
        $result = Div::getValuesBeforeBrackets($start);
        $this->assertEquals($result, $expectedResult);
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
            $string = Div::getRandomString($length, $addUpperCase, $addSpecialCharacters);
            if ($addUpperCase) {
                $regex = '~[a-zA-Z0-9]{' . $length . '}~';
            } else {
                $regex = '~[a-z0-9]{' . $length . '}~';
            }
            $this->assertSame(1, preg_match($regex, $string));
        }
    }
}
