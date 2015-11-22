<?php
namespace In2code\Femanager\Finisher;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Service\FinisherService;
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
 * Get all finishers classes and call finisher service for each of them
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class FinisherRunner
{

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * TypoScript settings
     *
     * @var array
     */
    protected $settings = array();

    /**
     * Own finisher classnames - ordering will be respected
     *
     * @var array
     */
    protected $ownFinisherClasses = array(
        'SaveToAnyTableFinisher',
        'SendParametersFinisher'
    );

    /**
     * Call finisher classes after submit
     *
     * @param User $user
     * @param string $actionMethodName
     * @param array $settings
     * @param ContentObjectRenderer $contentObject
     * @return void
     */
    public function callFinishers(
        User $user,
        $actionMethodName,
        $settings,
        ContentObjectRenderer $contentObject
    ) {
        $this->initialize($settings, $contentObject);
        $this->callLocalFinishers($user, $actionMethodName);
        $this->callForeignFinishers($user, $actionMethodName);
    }

    /**
     * Call own finisher classes after submit
     *
     * @param User $user
     * @param string $actionMethodName
     * @return void
     */
    protected function callLocalFinishers(User $user, $actionMethodName = null)
    {
        $ownClasses = $this->getOwnFinisherClasses();
        foreach ($ownClasses as $className) {
            /** @var FinisherService $finisherService */
            $finisherService = $this->objectManager->get(
                'In2code\\Femanager\\Domain\\Service\\FinisherService',
                $user,
                $this->settings,
                $this->contentObject
            );
            $finisherService->setClass(__NAMESPACE__ . '\\' . $className);
            $finisherService->setRequirePath(null);
            $finisherService->setConfiguration(array());
            $finisherService->setActionMethodName($actionMethodName);
            $finisherService->start();
        }
    }

    /**
     * Call foreign finisher classes after submit
     *
     * @param User $user
     * @param string $actionMethodName
     * @return void
     */
    protected function callForeignFinishers(User $user, $actionMethodName = null)
    {
        if (is_array($this->settings['finishers'])) {
            foreach ($this->settings['finishers'] as $finisherSettings) {
                /** @var FinisherService $finisherService */
                $finisherService = $this->objectManager->get(
                    'In2code\\Femanager\\Domain\\Service\\FinisherService',
                    $user,
                    $this->settings,
                    $this->contentObject
                );
                $finisherService->setClass($finisherSettings['class']);
                $finisherService->setRequirePath((string) $finisherSettings['require']);
                $finisherService->setConfiguration((array) $finisherSettings['config']);
                $finisherService->setActionMethodName($actionMethodName);
                $finisherService->start();
            }
        }
    }

    /**
     * Get all finisher classes in same directory
     *
     * @return array
     */
    public function getOwnFinisherClasses()
    {
        return $this->ownFinisherClasses;
    }

    /**
     * Initialize
     *
     * @param array $settings
     * @param ContentObjectRenderer $contentObject
     * @return void
     */
    public function initialize(array $settings, ContentObjectRenderer $contentObject)
    {
        $this->settings = $settings;
        $this->contentObject = $contentObject;
    }
}
