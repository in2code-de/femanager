<?php
namespace In2\Femanager\ViewHelpers\Misc;

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
 */
class RequestViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Get a GET or POST parameter
	 *
	 * @param \string $parameter			like tx_ext_pi1|list|field
	 * @param \bool $htmlspecialchars		Enable/Disabe htmlspecialchars
	 * @return \string
	 */
	public function render($parameter, $htmlspecialchars = TRUE) {
		// allow only normal characters
		$parameter = preg_replace('/[^a-zA-Z0-9_-\|]/', '', $parameter);
		// replace | with ][
		$string = str_replace('|', '\'][\'', $parameter);
		// create $_REQUEST string
		$string = '$_REQUEST[\'' . $string . '\']';
		// create variable
		eval('$value = ' . $string . ';');
		if ($htmlspecialchars) {
			$value = htmlspecialchars($value);
		}
		return $value;
	}
}