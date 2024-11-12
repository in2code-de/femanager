<?php

declare(strict_types=1);

namespace In2code\Femanager\Tests\Unit\Fixture\Utility;

use In2code\Femanager\Utility\AbstractUtility as AbstractUtilityFemanager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class AbstractUtility
 */
class AbstractUtility extends AbstractUtilityFemanager
{
    /**
     * @param string $tableName
     * @return array|mixed
     */
    public static function getTcaFromTablePublic($tableName): array
    {
        return self::getTcaFromTable($tableName);
    }

    public static function getFilesArrayPublic(): array
    {
        return self::getFilesArray();
    }

    public static function getUserGroupRepositoryPublic(): \In2code\Femanager\Domain\Repository\UserGroupRepository
    {
        return self::getUserGroupRepository();
    }

    /**
     * @return TypoScriptFrontendController
     */
    public static function getTypoScriptFrontendControllerPublic()
    {
        return self::getTypoScriptFrontendController();
    }

    public static function getContentObjectPublic(): \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
    {
        return self::getContentObject();
    }

    public static function getConfigurationManagerPublic(): \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
    {
        return self::getConfigurationManager();
    }
}
