<?php
declare(strict_types=1);
namespace In2code\Femanager\Persistence\Generic\Mapper;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Model\UserGroup;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap as DataMapExtbase;

/**
 * Disable tx_extbase_type='0' in where clause for femanager
 */
class DataMap extends DataMapExtbase
{

    /**
     * Disable record type for femanager
     *
     * @param string $recordType The record type
     * @return void
     */
    public function setRecordType($recordType)
    {
        parent::setRecordType($recordType);
        if ($this->getClassName() === User::class || $this->getClassName() === UserGroup::class) {
            $this->recordType = null;
        }
    }
}
