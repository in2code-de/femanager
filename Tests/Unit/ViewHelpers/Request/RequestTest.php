<?php
namespace In2\Femanager\ViewHelpers\Request;

use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;

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
 *
 * @package In2\Femanager\ViewHelpers\Form
 */
class RequestTest extends BaseTestCase {

	/**
	 * @var \In2\Femanager\ViewHelpers\Misc\RequestViewHelper
	 */
	protected $generalValidatorMock;

	/**
	 * @return void
	 */
	public function setUp() {
		$this->generalValidatorMock = $this->getAccessibleMock(
			'\In2\Femanager\ViewHelpers\Misc\RequestViewHelper',
			array('dummy')
		);
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		unset($this->generalValidatorMock);
	}

	/**
	 * Data Provider for renderReturnsString()
	 *
	 * @return array
	 */
	public function renderReturnsStringDataProvider() {
		return array(
			array(
				'L',
				TRUE,
				array(
					'L' => '123'
				),
				'123'
			),
			array(
				'test',
				TRUE,
				array(
					'test' => '>'
				),
				'&gt;'
			),
			array(
				'tx_test|sword',
				TRUE,
				array(
					'tx_test' => array(
						'sword' => 'abc'
					)
				),
				'abc'
			),
			array(
				'tx_test_pi1|abc|def',
				TRUE,
				array(
					'tx_test_pi1' => array(
						'abc' => array(
							'def' => 'xyz'
						)
					)
				),
				'xyz'
			),
			array(
				'asfd|abc|def|ghi',
				TRUE,
				array(
					'asfd' => array(
						'abc' => array(
							'def' => array(
								'ghi' => '7x'
							)
						)
					)
				),
				'7x'
			),
			array(
				'abc',
				TRUE,
				array(),
				''
			),
		);
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
	public function renderReturnsString($parameter, $htmlSpecialChars, $parametersToSet, $expectedResult) {
		$this->generalValidatorMock->_set('testVariables', $parametersToSet);
		$result = $this->generalValidatorMock->_call('render', $parameter, $htmlSpecialChars);
		$this->assertSame($expectedResult, $result);
	}
}