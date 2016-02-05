<?php
namespace In2code\Femanager\ViewHelpers\Request;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Alex Kellner <alexander.kellner@in2code.de>, in2code
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
 * Class RequestTest
 * @package In2code\Femanager\ViewHelpers\Request
 */
class RequestTest extends UnitTestCase
{

    /**
     * @var \In2code\Femanager\ViewHelpers\Misc\RequestViewHelper
     */
    protected $generalValidatorMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->generalValidatorMock = $this->getAccessibleMock(
            '\In2code\Femanager\ViewHelpers\Misc\RequestViewHelper',
            ['dummy']
        );
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        unset($this->generalValidatorMock);
    }

    /**
     * Data Provider for renderReturnsString()
     *
     * @return array
     */
    public function renderReturnsStringDataProvider()
    {
        return [
            [
                'L',
                true,
                [
                    'L' => '123'
                ],
                '123'
            ],
            [
                'test',
                true,
                [
                    'test' => '>'
                ],
                '&gt;'
            ],
            [
                'tx_test|sword',
                true,
                [
                    'tx_test' => [
                        'sword' => 'abc'
                    ]
                ],
                'abc'
            ],
            [
                'tx_test_pi1|abc|def',
                true,
                [
                    'tx_test_pi1' => [
                        'abc' => [
                            'def' => 'xyz'
                        ]
                    ]
                ],
                'xyz'
            ],
            [
                'asfd|abc|def|ghi',
                true,
                [
                    'asfd' => [
                        'abc' => [
                            'def' => [
                                'ghi' => '7x'
                            ]
                        ]
                    ]
                ],
                '7x'
            ],
            [
                'abc',
                true,
                [],
                ''
            ],
        ];
    }

    /**
     * Test for render()
     *
     * @param string $parameter
     * @param bool $htmlSpecialChars
     * @param array $parametersToSet
     * @param string $expectedResult
     * @dataProvider renderReturnsStringDataProvider
     * @return void
     * @test
     */
    public function renderReturnsString($parameter, $htmlSpecialChars, $parametersToSet, $expectedResult)
    {
        $this->generalValidatorMock->_set('testVariables', $parametersToSet);
        $result = $this->generalValidatorMock->_call('render', $parameter, $htmlSpecialChars);
        $this->assertSame($expectedResult, $result);
    }
}
