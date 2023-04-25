<?php

declare(strict_types=1);
namespace In2code\Femanager\Domain\Repository;

use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\ForwardCompatibility\Result;
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
    public function getControllerNameByPluginSettings(int $contentIdentifier): string
    {
        $queryBuilder = ObjectUtility::getQueryBuilder(self::TABLE_NAME);
        $flexFormQuery = $queryBuilder
            ->select('pi_flexform')
            ->from(self::TABLE_NAME)->where('uid=' . $contentIdentifier)->executeQuery();
        if (! $flexFormQuery instanceof Result) {
            throw new \Exception(
                'Something went wrong while getting FlexForm-value from Query.',
                1638443805
            );
        }
        $flexForm = (string)$flexFormQuery->fetchOne();

        return $this->getViewFromFlexForm($flexForm);
    }

    /**
     * @param string $view can be "new", "edit" or "invitation"
     * @throws \Exception
     * @throws Exception
     */
    public function isPluginWithViewOnGivenPage(string $view, int $pageIdentifier): bool
    {
        $queryBuilder = ObjectUtility::getQueryBuilder(self::TABLE_NAME);
        $pluginsOnPage = $queryBuilder
            ->select('pi_flexform')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageIdentifier, PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter($this->viewToPlugin[$view]))
            )
            ->executeStatement();
        if ($pluginsOnPage == 0) {
            throw new \Exception(
                'Something went wrong while getting PluginConfigurations from query.',
                1638443806
            );
        } else {
            return true;
        }

        return false;
    }

    protected function getViewFromFlexForm(string $flexForm): string
    {
        $view = '';
        $settings = $this->flexFormService->convertFlexFormContentToArray($flexForm);
        if (!empty($settings['switchableControllerActions'])
            && in_array($settings['switchableControllerActions'], $this->scaString)) {
            $view = array_search($settings['switchableControllerActions'], $this->scaString);
        }
        if (! is_string($view)) {
            return '';
        }
        return $view;
    }

    /**
     * @throws LogicException
     */
    protected function isViewInPluginConfiguration(string $view, string $pluginConfiguration): bool
    {
        $flexFormArray = $this->flexFormService->convertFlexFormContentToArray($pluginConfiguration);
        if (array_key_exists($view, $this->scaString)) {
            return $this->scaString[$view] === $flexFormArray['switchableControllerActions'];
        }
        throw new LogicException('Given view is not allowed', 1541506310);
    }
}
