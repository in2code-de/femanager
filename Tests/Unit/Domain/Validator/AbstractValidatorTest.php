<?php
namespace In2\Femanager\Tests;

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
 * Test case for class \In2\Femanager\Domain\Validator\AbstractValidator
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html
 * 			GNU General Public License, version 3 or later
 */
class AbstractValidatorTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * @var \In2\Femanager\Domain\Validator\AbstractValidator
	 */
	protected $generalValidatorMock;

	/**
	 * Make object available
	 * @return void
	 */
	public function setUp() {
		$this->generalValidatorMock = $this->getAccessibleMock(
			'\In2\Femanager\Domain\Validator\AbstractValidator', array('dummy')
		);
	}

	/**
	 * Remove object
	 * @return void
	 */
	public function tearDown() {
		unset($this->generalValidatorMock);
	}

	/**
	 * Test vor validateRequired()
	 * @return void
	 * @test
	 */
	public function validateRequiredReturnsBool() {
		$empty = '';
		$emptyResult = $this->generalValidatorMock->_callRef('validateRequired', $empty);
		$this->assertFalse($emptyResult);

		$filled = 'in2code.de';
		$filledResult = $this->generalValidatorMock->_callRef('validateRequired', $filled);
		$this->assertTrue($filledResult);
	}

	/**
	 * Dataprovider
	 *
	 * @return array
	 */
	public function validateEmailReturnsBoolDataProvider() {
		return array(
			// #0
			array(
				'in2code.de',
				FALSE
			),

			// #1
			array(
				'',
				FALSE
			),

			// #2
			array(
				'alex@in2code.de',
				TRUE
			),

			// #3
			array(
				'alex@in2code.',
				FALSE
			),

			// #4
			array(
				'www.in2code.de',
				FALSE
			),

			// #5
			array(
				'test@www.in2code.de',
				TRUE
			),

			// #6
			array(
				'alex@test.test.in2code.de',
				TRUE
			),
		);
	}

	/**
	 * Test for validateEmail()
	 *
	 * @param \string $value
	 * @param \string $result
	 * @return void
	 * @dataProvider validateEmailReturnsBoolDataProvider
	 * @test
	 */
	public function validateEmailReturnsBool($value, $result) {
		$test = $this->generalValidatorMock->_callRef('validateEmail', $value);

		$this->assertSame($result, $test);
	}

	/**
	 * Test vor validateMin()
	 *
	 * @return void
	 * @test
	 */
	public function validateMinReturnsBool() {
		$value = 'in2code.de';
		$allowedLength = 10;
		$result = $this->generalValidatorMock->_callRef('validateMin', $value, $allowedLength);
		$this->assertTrue($result);

		$value = 'in2code.d';
		$result = $this->generalValidatorMock->_callRef('validateMin', $value, $allowedLength);
		$this->assertFalse($result);
	}

	/**
	 * Test vor validateMax()
	 *
	 * @return void
	 * @test
	 */
	public function validateMaxReturnsBool() {
		$value = 'in2code.de';
		$allowedLength = 10;
		$result = $this->generalValidatorMock->_callRef('validateMax', $value, $allowedLength);
		$this->assertTrue($result);

		$value = 'in2code.de.';
		$result = $this->generalValidatorMock->_callRef('validateMax', $value, $allowedLength);
		$this->assertFalse($result);
	}

	/**
	 * Dataprovider
	 *
	 * @return array
	 */
	public function validateIntReturnsBoolDataProvider() {
		return array(
			array(
				'123',
				TRUE
			),
			array(
				'1235135',
				TRUE
			),
			array(
				'123a23',
				FALSE
			),
			array(
				'123 23',
				FALSE
			),
			array(
				'12323,',
				FALSE
			),
			array(
				'12323²',
				FALSE
			),
			array(
				'3 ',
				FALSE
			)
		);
	}

	/**
	 * Test vor validateInt()
	 *
	 * @param string $value
	 * @param bool $expectedResult
	 * @return void
	 * @dataProvider validateIntReturnsBoolDataProvider
	 * @test
	 */
	public function validateIntReturnsBool($value, $expectedResult) {
		$this->assertSame(
			$expectedResult,
			$this->generalValidatorMock->_callRef('validateInt', $value)
		);
	}

	/**
	 * Dataprovider
	 *
	 * @return array
	 */
	public function validateLettersReturnsBoolDataProvider() {
		return array(
			array(
				'abafdbadsf',
				TRUE
			),
			array(
				'a_-b',
				TRUE
			),
			array(
				'abafd3adsf',
				FALSE
			),
			array(
				'abä',
				FALSE
			),
			array(
				'ab:',
				FALSE
			),
			array(
				'ab cd',
				FALSE
			)
		);
	}

	/**
	 * Test vor validateLetters()
	 *
	 * @param string $value
	 * @param bool $expectedResult
	 * @return void
	 * @dataProvider validateLettersReturnsBoolDataProvider
	 * @test
	 */
	public function validateLettersReturnsBool($value, $expectedResult) {
		$this->assertSame(
			$expectedResult,
			$this->generalValidatorMock->_callRef('validateLetters', $value)
		);
	}

	/**
	 * Dataprovider
	 *
	 * @return array
	 */
	public function validateMustIncludeReturnsBoolDataProvider() {
		return array(
			// #0
			array(
				'in2code.de',
				'number,letter,special',
				TRUE
			),

			// #1
			array(
				'in2code.de',
				'number,  special',
				TRUE
			),

			// #2
			array(
				'in2code.de',
				'   special  ,   letter ',
				TRUE
			),

			// #3
			array(
				'in2code',
				'number,letter',
				TRUE
			),

			// #4
			array(
				'in2code',
				'special,letter',
				FALSE
			),

			// #5
			array(
				'in2code',
				'number',
				TRUE
			),

			// #6
			array(
				'incode.',
				'number,letter',
				FALSE
			),
		);
	}

	/**
	 * Test for validateMustInclude()
	 *
	 * @param \string $value
	 * @param \string $configuration
	 * @param \string $result
	 * @return void
	 * @dataProvider validateMustIncludeReturnsBoolDataProvider
	 * @test
	 */
	public function validateMustIncludeReturnsBool($value, $configuration, $result) {
		$test = $this->generalValidatorMock->_callRef('validateMustInclude', $value, $configuration);

		$this->assertSame($result, $test);
	}

	/**
	 * Test vor validateInList()
	 *
	 * @return void
	 * @test
	 */
	public function validateInListReturnsBool() {
		$value = '2';
		$configuration = '1,2,5,8';
		$result = $this->generalValidatorMock->_callRef('validateInList', $value, $configuration);
		$this->assertTrue($result);

		$value = 'ghi';
		$configuration = 'abc,def,ghi';
		$result = $this->generalValidatorMock->_callRef('validateInList', $value, $configuration);
		$this->assertTrue($result);

		$value = '23';
		$configuration = '1,2,3';
		$result = $this->generalValidatorMock->_callRef('validateInList', $value, $configuration);
		$this->assertFalse($result);
	}

	/**
	 * Dataprovider
	 *
	 * @return array
	 */
	public function validateSameAsReturnsBoolDateProvider() {
		return array(
			// #0
			array(
				'abcd',
				'abcd',
				TRUE
			),

			// #1
			array(
				'a',
				'b',
				FALSE
			),

			// #2
			array(
				'a',
				'',
				FALSE
			),

			// #3
			array(
				'',
				'',
				TRUE
			),
		);
	}

	/**
	 * Test for validateSameAs()
	 *
	 * @param \string $value
	 * @param \string $value2
	 * @param \string $result
	 * @return void
	 * @dataProvider validateSameAsReturnsBoolDateProvider
	 * @test
	 */
	public function validateSameAsReturnsBool($value, $value2, $result) {
		$test = $this->generalValidatorMock->_callRef('validateSameAs', $value, $value2);
		$this->assertSame($result, $test);
	}

}