<?php
declare(strict_types=1);
namespace In2code\Femanager\Domain\Repository;

use In2code\Femanager\Utility\ObjectUtility;

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
            ->where('pid=' . (int)$pageIdentifier)
            ->execute()
            ->fetchAll();
        foreach ($pluginConfigurations as $pluginConfiguration) {
            if ($this->isViewInPluginConfiguration($view, $pluginConfiguration['pi_flexform'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $view
     * @param string $pluginConfiguration
     * @return bool
     */
    protected function isViewInPluginConfiguration(string $view, string $pluginConfiguration): bool
    {
        if (array_key_exists($view, $this->scaString)) {
            $viewString = $this->scaString[$view];
            preg_match(
                '~<field index="switchableControllerActions">\s+<value index="vDEF">'
                    . htmlspecialchars($viewString) . '~',
                $pluginConfiguration,
                $result
            );
            return $result !== [];
        } else {
            throw new \LogicException('Given view is not allowed', 1541506310);
        }
    }
}
