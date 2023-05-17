<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Repository;

use Exception;
use In2code\Femanager\Domain\Service\PluginService;
use In2code\Femanager\Utility\ObjectUtility;
use PDO;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PluginRepository
 */
class PluginRepository
{
    final public const TABLE_NAME = 'tt_content';

    protected FlexFormService $flexFormService;

    /**
     * @param FlexFormService|null $flexFormService
     */
    public function __construct(FlexFormService $flexFormService = null)
    {
        $this->flexFormService = $flexFormService ?? GeneralUtility::makeInstance(FlexFormService::class);
    }

    /**
     * @throws Exception
     */
    public function isPluginWithViewOnGivenPage(int $pageIdentifier, string $pluginName): bool
    {
        $pluginService = GeneralUtility::makeInstance(PluginService::class);
        $allowedPlugins = $pluginService->getAllowedPlugins();

        if (in_array($pluginName, $allowedPlugins)) {
            $queryBuilder = ObjectUtility::getQueryBuilder(self::TABLE_NAME);
            $cType = str_replace('tx_', '', $pluginName);
            $pluginOnPageQuery = $queryBuilder
                ->select('uid')
                ->from(self::TABLE_NAME)
                ->where(
                    $queryBuilder->expr()->eq(
                        'pid',
                        $queryBuilder->createNamedParameter($pageIdentifier, PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'CType',
                        $queryBuilder->createNamedParameter($cType, PDO::PARAM_STR)
                    )
                )
                ->executeQuery();
            return count($pluginOnPageQuery->fetchAllAssociative()) > 0;
        }

        return false;
    }
}
