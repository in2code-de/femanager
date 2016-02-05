<?php
namespace In2code\Femanager\Tests\Domain\Model;

use In2code\Femanager\Domain\Model\User;
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
 * Class UserTest
 * @package In2code\Femanager\Tests\Domain\Model
 */
class UserTest extends UnitTestCase
{

    /**
     * @var User
     */
    protected $fixture;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->fixture = new User();
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        unset($this->fixture);
    }

    /**
     * @test
     * @return void
     */
    public function getUsernameReturnsInitialValueForString()
    {
    }

    /**
     * @test
     * @return void
     */
    public function setUsernameForStringSetsUsername()
    {
        $this->fixture->setUsername('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->fixture->getUsername());
    }

}
