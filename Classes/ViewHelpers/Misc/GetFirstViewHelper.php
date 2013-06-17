<?php
namespace In2\Femanager\ViewHelpers\Misc;

/**
 * View helper to get first subobject of objectstorage
 *
 * @package TYPO3
 * @subpackage Fluid
 */
class GetFirstViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * View helper to get first subobject of objectstorage
	 *
	 * @param \object $objectStorage
	 * @return \object
	 */
	public function render($objectStorage) {
		foreach ($objectStorage as $object) {
			return $object;
		}
	}
}

?>