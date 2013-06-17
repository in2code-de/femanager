<?php
namespace In2\Femanager\ViewHelpers\Misc;

/**
 * View helper like ucfirst()
 *
 * @package TYPO3
 * @subpackage Fluid
 */
class UpperViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * View helper like ucfirst()
	 *
	 * @param \string $string
	 * @return \string
	 */
	public function render($string = '') {
		return ucfirst($string);
	}
}

?>