<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Service;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageTreeService
{
    /**
     * Copied and adapted from QueryGenerator::getTreeList to solve deprecation #92080
     * https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/11.0/Deprecation-92080-DeprecatedQueryGeneratorAndQueryView.html
     *
     * Recursively fetch all descendants of a given page
     *
     * @param int $pageUid uid of the page
     * @param int $depth
     * @param int $begin
     * @param string $permClause
     * @return string comma separated list of descendant pages
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getTreeList($pageUid, $depth, $begin = 0, $permClause = ''): int|string|float
    {
        $depth = (int)$depth;
        $begin = (int)$begin;
        $pageUid = (int)$pageUid;
        if ($pageUid < 0) {
            $pageUid = abs($pageUid);
        }

        $theList = $begin === 0 ? $pageUid : '';

        if ($pageUid && $depth > 0) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
            $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
            $queryBuilder->select('uid')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->eq(
                        'pid',
                        $queryBuilder->createNamedParameter($pageUid, Connection::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq('sys_language_uid', 0)
                )
                ->orderBy('uid');
            if ($permClause !== '') {
                $queryBuilder->andWhere(QueryHelper::stripLogicalOperatorPrefix($permClause));
            }

            $statement = $queryBuilder->executeQuery();
            while ($row = $statement->fetchAssociative()) {
                if ($begin <= 0) {
                    $theList .= ',' . $row['uid'];
                }

                if ($depth > 1) {
                    $theSubList = $this->getTreeList($row['uid'], $depth - 1, $begin - 1, $permClause);
                    if (!empty($theList) && !empty($theSubList) && ($theSubList[0] !== ',')) {
                        $theList .= ',';
                    }

                    $theList .= $theSubList;
                }
            }
        }

        return $theList;
    }
}
