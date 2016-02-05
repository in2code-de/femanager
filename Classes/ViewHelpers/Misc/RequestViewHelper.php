<?php
namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

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
 * Class RequestViewHelper
 *
 * @package In2code\Femanager\ViewHelpers\Misc
 */
class RequestViewHelper extends AbstractViewHelper
{

    /**
     * @var array|string
     */
    protected $variable = [];

    /**
     * @var int
     */
    protected $depth = 1;

    /**
     * @var array
     */
    protected $testVariables = null;

    /**
     * Get a GET or POST parameter
     *
     * @param string $parameter like tx_ext_pi1|list|field
     * @param bool $htmlspecialchars Enable/Disable htmlspecialchars
     * @return string
     */
    public function render($parameter, $htmlspecialchars = true)
    {
        $parts = $this->init($parameter);
        $result = $this->getVariableFromDepth($parts);
        if ($htmlspecialchars) {
            $result = htmlspecialchars($result);
        }
        return $result;
    }

    /**
     * @param array $param
     * @return array|string
     */
    protected function getVariableFromDepth(array $param)
    {
        if (is_array($this->variable)) {
            $this->variable = $this->variable[$param[$this->depth]];
            $this->depth++;
            $this->getVariableFromDepth($param);
        }
        return $this->variable;
    }

    /**
     * Initially sets $this->variable
     *
     * @param $parameter
     * @return array
     */
    protected function init($parameter)
    {
        $parts = explode('|', $parameter);
        $this->variable = GeneralUtility::_GP($parts[0]);
        if ($this->testVariables) {
            $this->variable = $this->testVariables[$parts[0]];
        }
        return $parts;
    }
}
