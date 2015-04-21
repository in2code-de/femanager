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
			'\In2\Femanager\Domain\Validator\AbstractValidator',
			array('dummy')
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
	 * Dataprovider for validateRequiredReturnsBool()
	 *
	 * @return array
	 */
	public function validateRequiredReturnsBoolDataProvider() {
		return array(
			array(
				'in2code.de',
				TRUE
			),
			array(
				'.',
				TRUE
			),
			array(
				1234,
				TRUE
			),
			array(
				1234.56,
				TRUE
			),
			array(
				'',
				FALSE
			),
			array(
				array(),
				FALSE
			),
			array(
				'0',
				FALSE
			),
			array(
				0,
				FALSE
			),
			array(
				NULL,
				FALSE
			),
			array(
				FALSE,
				FALSE
			)
		);
	}

	/**
	 * Test vor validateRequired()
	 *
	 * @param string $value
	 * @param string $expectedResult
	 * @return void
	 * @dataProvider validateRequiredReturnsBoolDataProvider
	 * @test
	 */
	public function validateRequiredReturnsBool($value, $expectedResult) {
		$this->assertSame(
			$expectedResult,
			$this->generalValidatorMock->_callRef('validateRequired', $value)
		);
	}

	/**
	 * Dataprovider for validateEmailReturnsBool()
	 *
	 * @return array
	 */
	public function validateEmailReturnsBoolDataProvider() {
		return array(
			array(
				'in2code.de',
				FALSE
			),
			array(
				'',
				FALSE
			),
			array(
				'alex@in2code.de',
				TRUE
			),
			array(
				'alex@in2code.',
				FALSE
			),
			array(
				'www.in2code.de',
				FALSE
			),
			array(
				'test@www.in2code.de',
				TRUE
			),
			array(
				'alex@test.test.in2code.de',
				TRUE
			),
		);
	}

	/**
	 * Test for validateEmail()
	 *
	 * @param string $value
	 * @param string $expectedResult
	 * @return void
	 * @dataProvider validateEmailReturnsBoolDataProvider
	 * @test
	 */
	public function validateEmailReturnsBool($value, $expectedResult) {
		$this->assertSame(
			$expectedResult,
			$this->generalValidatorMock->_callRef('validateEmail', $value)
		);
	}

	/**
	 * Dataprovider for validateMinReturnsBool()
	 *
	 * @return array
	 */
	public function validateMinReturnsBoolDataProvider() {
		return array(
			array(
				'in2code.de',
				10,
				TRUE
			),
			array(
				'in2code.d',
				10,
				FALSE
			),
			array(
				'i',
				1,
				TRUE
			),
			array(
				'i',
				2,
				FALSE
			),
			array(
				' i ',
				2,
				TRUE
			)
		);
	}

	/**
	 * Test vor validateMin()
	 *
	 * @param string $value
	 * @param int $allowedLength
	 * @param string $expectedResult
	 * @return void
	 * @dataProvider validateMinReturnsBoolDataProvider
	 * @test
	 */
	public function validateMinReturnsBool($value, $allowedLength, $expectedResult) {
		$this->assertSame(
			$expectedResult,
			$this->generalValidatorMock->_callRef('validateMin', $value, $allowedLength)
		);
	}

	/**
	 * Dataprovider for validateMaxReturnsBool()
	 *
	 * @return array
	 */
	public function validateMaxReturnsBoolDataProvider() {
		return array(
			array(
				'in2code.de',
				10,
				TRUE
			),
			array(
				'in2code.de.',
				10,
				FALSE
			),
			array(
				'i',
				1,
				TRUE
			),
			array(
				'i',
				2,
				TRUE
			),
			array(
				' i ',
				2,
				FALSE
			),
			array(
				'i',
				0,
				FALSE
			)
		);
	}

	/**
	 * Test vor validateMax()
	 *
	 * @param string $value
	 * @param int $allowedLength
	 * @param string $expectedResult
	 * @return void
	 * @dataProvider validateMaxReturnsBoolDataProvider
	 * @test
	 */
	public function validateMaxReturnsBool($value, $allowedLength, $expectedResult) {
		$this->assertSame(
			$expectedResult,
			$this->generalValidatorMock->_callRef('validateMax', $value, $allowedLength)
		);
	}

	/**
	 * Dataprovider for validateIntReturnsBool()
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
	 * Dataprovider for validateLettersReturnsBool()
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
	 * Dataprovider for validateMustIncludeReturnsBool()
	 *
	 * @return array
	 */
	public function validateMustIncludeReturnsBoolDataProvider() {
		return array(
			array(
				'in2code.de',
				'number,letter,special',
				TRUE
			),
			array(
				'in2code.de ',
				'number,letter,special,space',
				TRUE
			),
			array(
				'in2code.de',
				'number,  special',
				TRUE
			),
			array(
				'in2code.de',
				'   special  ,   letter ',
				TRUE
			),
			array(
				'in2code',
				'number,letter',
				TRUE
			),
			array(
				'in2code',
				'special,letter',
				FALSE
			),
			array(
				'in2code#',
				'special',
				TRUE
			),
			array(
				'in2co de',
				'special',
				TRUE
			),
			array(
				'in2code',
				'number',
				TRUE
			),
			array(
				'incode.',
				'number,letter',
				FALSE
			),
			array(
				'in2 code',
				'number,letter',
				TRUE
			),
			array(
				'in code',
				'letter',
				TRUE
			),
			array(
				'1 2',
				'number',
				TRUE
			),
			array(
				'2',
				'number',
				TRUE
			),
			array(
				'1 2',
				'space',
				TRUE
			),
			array(
				'132',
				'space',
				FALSE
			),
			array(
				'a;#/%äß´^á 3',
				'space',
				TRUE
			),
			array(
				'a;#/%äß´^á 3',
				'letter,number,special,space',
				TRUE
			),
			array(
				'a;#/%äß´^á 3',
				'special,space',
				TRUE
			),
		);
	}

	/**
	 * Test for validateMustInclude()
	 *
	 * @param string $value
	 * @param string $configuration
	 * @param string $expectedResult
	 * @return void
	 * @dataProvider validateMustIncludeReturnsBoolDataProvider
	 * @test
	 */
	public function validateMustIncludeReturnsBool($value, $configuration, $expectedResult) {
		$this->assertSame(
			$expectedResult,
			$this->generalValidatorMock->_callRef('validateMustInclude', $value, $configuration)
		);
	}

	/**
	 * Dataprovider for validateMustNotIncludeReturnsBool()
	 *
	 * @return array
	 */
	public function validateMustNotIncludeReturnsBoolDataProvider() {
		return array(
			array(
				'in2code.de',
				'number,letter,special',
				FALSE
			),
			array(
				'in2code.de ',
				'number,letter,special,space',
				FALSE
			),
			array(
				'in2code.de',
				'number,  special',
				FALSE
			),
			array(
				'in2code.de',
				'   special  ,   letter ',
				FALSE
			),
			array(
				'in2code',
				'number,letter',
				FALSE
			),
			array(
				'in2code',
				'special,space',
				TRUE
			),
			array(
				'in2code#',
				'special',
				FALSE
			),
			array(
				'in2co3de',
				'special',
				TRUE
			),
			array(
				'in2code',
				'number',
				FALSE
			),
			array(
				'incode.',
				'number,letter',
				FALSE
			),
			array(
				'in2 code',
				'number,letter',
				FALSE
			),
			array(
				'in code',
				'letter',
				FALSE
			),
			array(
				'1 2',
				'number',
				FALSE
			),
			array(
				'2',
				'number',
				FALSE
			),
			array(
				'1 2',
				'space',
				FALSE
			),
			array(
				'132',
				'space',
				TRUE
			),
			array(
				'a;#/%äß´^á 3',
				'space',
				FALSE
			),
			array(
				'a;#/%äß´^á 3',
				'letter,number,special,space',
				FALSE
			),
			array(
				'a;#/%äß´^á 3',
				'special,space',
				FALSE
			),
		);
	}

	/**
	 * Test for validateMustNotInclude()
	 *
	 * @param string $value
	 * @param string $configuration
	 * @param string $expectedResult
	 * @return void
	 * @dataProvider validateMustNotIncludeReturnsBoolDataProvider
	 * @test
	 */
	public function validateMustNotIncludeReturnsBool($value, $configuration, $expectedResult) {
		$this->assertSame(
			$expectedResult,
			$this->generalValidatorMock->_callRef('validateMustNotInclude', $value, $configuration)
		);
	}

	/**
	 * Dataprovider for validateInListReturnsBool()
	 *
	 * @return array
	 */
	public function validateInListReturnsBoolDataProvider() {
		return array(
			array(
				'2',
				'1,2,5,8',
				TRUE
			),
			array(
				'2',
				'1,1,2',
				TRUE
			),
			array(
				'1',
				'1,3,2',
				TRUE
			),
			array(
				'1',
				'1,3,2',
				TRUE
			),
			array(
				'1',
				1,
				TRUE
			),
			array(
				1,
				'1,2',
				TRUE
			),
			array(
				'a',
				'a',
				TRUE
			),
			array(
				'23',
				'1,234,3',
				FALSE
			),
			array(
				'a',
				'ab',
				FALSE
			),
			array(
				'a',
				'ba',
				FALSE
			),
			array(
				'a',
				'bac',
				FALSE
			),
		);
	}

	/**
	 * Test vor validateInList()
	 *
	 * @param string $value
	 * @param string $configuration
	 * @param string $expectedResult
	 * @return void
	 * @dataProvider validateInListReturnsBoolDataProvider
	 * @test
	 */
	public function validateInListReturnsBool($value, $configuration, $expectedResult) {
		$this->assertSame(
			$expectedResult,
			$this->generalValidatorMock->_callRef('validateInList', $value, $configuration)
		);
	}

	/**
	 * Dataprovider for validateSameAsReturnsBool()
	 *
	 * @return array
	 */
	public function validateSameAsReturnsBoolDateProvider() {
		return array(
			array(
				'abcd',
				'abcd',
				TRUE
			),
			array(
				'a',
				'b',
				FALSE
			),
			array(
				'a',
				'',
				FALSE
			),
			array(
				'',
				'',
				TRUE
			),
			array(
				0,
				'0',
				FALSE
			),
			array(
				1,
				'1',
				FALSE
			),
		);
	}

	/**
	 * Test for validateSameAs()
	 *
	 * @param string $value
	 * @param string $value2
	 * @param string $result
	 * @return void
	 * @dataProvider validateSameAsReturnsBoolDateProvider
	 * @test
	 */
	public function validateSameAsReturnsBool($value, $value2, $result) {
		$test = $this->generalValidatorMock->_callRef('validateSameAs', $value, $value2);
		$this->assertSame($result, $test);
	}
}