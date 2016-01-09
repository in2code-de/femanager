<?php
namespace In2code\Femanager\Tests\Utility;

use In2code\Femanager\Utility\FileUtility;
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
 * Class FileUtilityTest
 * @package In2code\Femanager\Tests\Utility
 */
class FileUtilityTest extends UnitTestCase
{

    /**
     * Dataprovider for checkExtension()
     *
     * @return array
     */
    public function checkExtensionReturnBoolDataProvider()
    {
        return [
            [
                'theImage_dot.com',
                false,
            ],
            [
                'theImage_dot.com.jpg',
                true,
            ],
            [
                'test.ImagetheImage_dot.com.JPEG',
                true,
            ],
            [
                'test.png',
                true,
            ],
            [
                'SoNenntEinRedakteurEineDätaei.PNG',
                true,
            ],
            [
                'test.phx.bmp',
                true,
            ],
            [
                'test.php.bmp',
                false,
            ],
            [
                'test.phtml.bmp',
                false,
            ],
        ];
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
        $result = FileUtility::checkExtension($givenValue);
        $this->assertEquals($result, $expectedResult);
    }
}
