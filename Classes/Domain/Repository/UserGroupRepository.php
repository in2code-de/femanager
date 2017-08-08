<?php
declare(strict_types=1);
namespace In2code\Femanager\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class UserGroupRepository
 */
class UserGroupRepository extends Repository
{

    /**
     * Find all groups and respect exclude list
     *
     * @param string $removeList commaseparated list
     * @return QueryResultInterface
     */
    public function findAllForFrontendSelection($removeList)
    {
        $query = $this->createQuery();
        if ($removeList) {
            $query->matching($query->logicalNot($query->in('uid', explode(',', $removeList))));
        }
        $query->setOrderings(['title' => QueryInterface::ORDER_ASCENDING]);
        return $query->execute();
    }
}
