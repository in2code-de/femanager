<?php
namespace In2\Femanager\Persistence\Generic\Mapper;

/**
 * Disable tx_extbase_type='0' in where clause for femanager
 */
class DataMap extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap {

	/**
	 * Sets the record type
	 *
	 * @param string $recordType The record type
	 * @return void
	 */
	public function setRecordType($recordType) {
		parent::setRecordType($recordType);

		if (
			$this->getClassName() === 'In2\\Femanager\\Domain\\Model\\User' ||
			$this->getClassName() === 'In2\\Femanager\\Domain\\Model\\UserGroup'
		) {
			$this->recordType = NULL;
		}
	}
}