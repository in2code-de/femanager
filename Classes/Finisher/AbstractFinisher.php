<?php
namespace In2code\Femanager\Finisher;

use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Alex Kellner <alexander.kellner@in2code.de>, in2code.de
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 * AbstractFinisher
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractFinisher implements FinisherInterface
{

    /**
     * @var User
     */
    protected $user;

    /**
     * Extension settings
     *
     * @var array
     */
    protected $settings;

    /**
     * Finisher service configuration
     *
     * @var array
     */
    protected $configuration;

    /**
     * Controller actionName - usually "createAction" or "confirmationAction"
     *
     * @var null
     */
    protected $actionMethodName = null;

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return AbstractFinisher
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     * @return AbstractFinisher
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param array $configuration
     * @return AbstractFinisher
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return null
     */
    public function getActionMethodName()
    {
        return $this->actionMethodName;
    }

    /**
     * @param null $actionMethodName
     * @return AbstractFinisher
     */
    public function setActionMethodName($actionMethodName)
    {
        $this->actionMethodName = $actionMethodName;
        return $this;
    }

    /**
     * @return void
     */
    public function initializeFinisher()
    {
    }

    /**
     * @param User $user
     * @param array $configuration
     * @param array $settings
     * @param ContentObjectRenderer $contentObject
     * @param string $actionMethodName
     */
    public function __construct(
        User $user,
        array $configuration,
        array $settings,
        $actionMethodName,
        ContentObjectRenderer $contentObject
    ) {
        $this->setUser($user);
        $this->setConfiguration($configuration);
        $this->setSettings($settings);
        $this->setActionMethodName($actionMethodName);
        $this->contentObject = $contentObject;
    }
}
