<?php
namespace In2\Femanager\ViewHelpers\Misc;

/**
 * View helper like json_encode()
 *
 * @package TYPO3
 * @subpackage Fluid
 */
class JsonEncodeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * View helper like json_encode()
	 *
	 * @param \array $array
	 * @return \string
	 */
	public function render($array) {
		return json_encode($array);
	}
}