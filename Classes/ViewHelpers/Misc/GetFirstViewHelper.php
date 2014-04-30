<?php
namespace In2\Femanager\ViewHelpers\Misc;

/**
 * View helper to get first subobject of objectstorage
 *
 * @package TYPO3
 * @subpackage Fluid
 */
class GetFirstViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper {

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerUniversalTagAttributes();
	}

	/**
	 * View helper to get first subobject of objectstorage
	 *
	 * @param \object $objectStorage
	 * @return \mixed
	 */
	public function render($objectStorage) {
		if ($objectStorage === NULL) {
			return NULL;
		}
		foreach ($objectStorage as $object) {
			return $object;
		}

		// try to get value from originalRequest
		if ($this->configurationManager->isFeatureEnabled('rewrittenPropertyMapper')) {
			// seperate if version is 6.2 or lower
			if (
				(method_exists($this, 'hasMappingErrorOccured') && $this->hasMappingErrorOccured()) ||
				(method_exists($this, 'hasMappingErrorOccurred') && $this->hasMappingErrorOccurred())
			) {
				return $this->getValue();
			}
		}

		return NULL;
	}
}