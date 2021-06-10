<?php
declare(strict_types = 1);
namespace In2code\Femanager\Domain\Repository;

use In2code\Femanager\Utility\ObjectUtility;
use TYPO3\CMS\Core\Service\FlexFormService;

/**
 * Class PluginRepository
 */
class PluginRepository
{
    const TABLE_NAME = 'tt_content';

    /**
     * @var array
     */
    protected $scaString = [
        'new' => 'New->new;New->create;New->createStatus;New->confirmCreateRequest;',
        'edit' => 'Edit->edit;Edit->update;Edit->delete;Edit->confirmUpdateRequest;User->imageDelete;',
        'invitation' => 'Invitation->new;Invitation->create;Invitation->edit;'
            . 'Invitation->update;Invitation->delete;Invitation->status;',
    ];

    /**
     * @param int $contentIdentifier
     * @return string
     */
    public function getControllerNameByPluginSettings(int $contentIdentifier): string
    {
        $queryBuilder = ObjectUtility::getQueryBuilder(self::TABLE_NAME);
        $flexForm = (string)$queryBuilder
            ->select('pi_flexform')
            ->from(self::TABLE_NAME)
            ->where('uid=' . (int)$contentIdentifier)
            ->execute()
            ->fetchColumn(0);
        return $this->getViewFromFlexForm((string)$flexForm);
    }

    /**
     * @param string $view can be "new", "edit" or "invitation"
     * @param int $pageIdentifier
     * @return bool
     */
    public function isPluginWithViewOnGivenPage(string $view, int $pageIdentifier): bool
    {
        $queryBuilder = ObjectUtility::getQueryBuilder(self::TABLE_NAME);
        $pluginConfigurations = $queryBuilder
            ->select('pi_flexform')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageIdentifier, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('list')),
                $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter('femanager_pi1'))
            )
            ->execute()
            ->fetchAll();
        foreach ($pluginConfigurations as $pluginConfiguration) {
            if ($this->isViewInPluginConfiguration($view, (string)$pluginConfiguration['pi_flexform'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $flexForm
     * @return string
     */
    protected function getViewFromFlexForm(string $flexForm): string
    {
        $view = '';
        $flexFormService = ObjectUtility::getObjectManager()->get(FlexFormService::class);
        $settings = $flexFormService->convertFlexFormContentToArray($flexForm);
        if (!empty($settings['switchableControllerActions'])
            && in_array($settings['switchableControllerActions'], $this->scaString)) {
            $view = array_search($settings['switchableControllerActions'], $this->scaString);
        }
        return $view;
    }

    /**
     * @param string $view
     * @param string $pluginConfiguration
     * @return bool
     */
    protected function isViewInPluginConfiguration(string $view, string $pluginConfiguration): bool
    {
        $flexFormService = ObjectUtility::getObjectManager()->get(FlexFormService::class);
        $flexFormArray = $flexFormService->convertFlexFormContentToArray($pluginConfiguration);
        if (array_key_exists($view, $this->scaString)) {
            return $this->scaString[$view] === $flexFormArray['switchableControllerActions'];
        }
        throw new \LogicException('Given view is not allowed', 1541506310);
    }
}
