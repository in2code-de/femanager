<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Repository;

use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Result;
use In2code\Femanager\Domain\Service\PluginService;
use In2code\Femanager\Utility\ObjectUtility;
use LogicException;
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
     * @var array<string, string>
     */
    protected array $scaString = [
        'new' => 'New->new;New->create;New->createStatus;New->confirmCreateRequest;',
        'edit' => 'Edit->edit;Edit->update;Edit->delete;Edit->confirmUpdateRequest;User->imageDelete;',
        'invitation' => 'Invitation->new;Invitation->create;Invitation->edit;'
            . 'Invitation->update;Invitation->delete;Invitation->status;',
    ];

    protected array $viewToPlugin = [
        'new' => 'femanager_registration',
        'edit' => 'femanager_edit',
        'invitation' => 'femanager_invitation'
    ];

    /**
     * @param FlexFormService|null $flexFormService
     */
    public function __construct(FlexFormService $flexFormService = null)
    {
        $this->flexFormService = $flexFormService ?? GeneralUtility::makeInstance(FlexFormService::class);
    }

    /**
     * @throws \Exception
     * @throws Exception
     */
    public function getControllerNameByPageWithPlugin(int $contentIdentifier): string
    {
        $queryBuilder = ObjectUtility::getQueryBuilder(self::TABLE_NAME);
        $pluginQuery = $queryBuilder
            ->select('CType')
            ->from(self::TABLE_NAME)
            ->where('uid=' . $contentIdentifier);

        $result = $pluginQuery->executeQuery();
        if (!$result instanceof Result) {
            throw new \Exception(
                'Something went wrong while getting FlexForm-value from Query.',
                1638443805
            );
        }
        $pluginName = (string)$result->fetchOne();

        return $pluginName;
    }

    /**
     * @throws \Exception
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
            if (! $pluginOnPageQuery instanceof Result) {
                throw new \Exception(
                    'Something went wrong while getting PluginConfigurations from query.',
                    1638443806
                );
            }
            return count($pluginOnPageQuery->fetchAllAssociative()) > 0;
        }

        return false;
    }
}
