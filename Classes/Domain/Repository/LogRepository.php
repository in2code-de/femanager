<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Repository;

use DateTimeImmutable;
use Exception;
use TYPO3\CMS\Extbase\Persistence\Repository;

class LogRepository extends Repository
{
    public function findByFilter(array $filter): iterable
    {
        $constraints = [];
        $query = $this->createQuery();

        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setRespectSysLanguage(false);

        if (!empty($filter['user'] ?? '')) {
            $constraints['user'] = $query->equals('user', $filter['user']);
        }

        if (!empty($filter['state'] ?? '')) {
            $constraints['state'] = $query->equals('state', $filter['state']);
        }

        if (!empty($filter['fromDate'] ?? '')) {
            try {
                $constraints['from'] = $query->greaterThanOrEqual('tstamp', new DateTimeImmutable($filter['fromDate']));
            } catch (Exception $e) {
                // ignore filter
            }
        }

        if (!empty($filter['untilDate'] ?? '')) {
            try {
                $constraints['until'] = $query->lessThan('tstamp', new DateTimeImmutable($filter['untilDate']));
            } catch (Exception $e) {
                // ignore filter
            }

        }

        $query->matching($query->logicalAnd(...$constraints));

        return $query->execute();
    }
}
