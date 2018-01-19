<?php
declare(strict_types=1);
namespace In2code\Femanager\Tests\Unit\Fixture\Utility;

use In2code\Femanager\Domain\Repository\UserGroupRepository;
use In2code\Femanager\Utility\AbstractUtility as AbstractUtilityFemanager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
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
    public static function getTcaFromTablePublic($tableName)
    {
        return self::getTcaFromTable($tableName);
    }

    /**
     * @return array
     */
    public static function getFilesArrayPublic()
    {
        return self::getFilesArray();
    }

    /**
     * @return UserGroupRepository
     */
    public static function getUserGroupRepositoryPublic()
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

    /**
     * @return ContentObjectRenderer
     */
    public static function getContentObjectPublic()
    {
        return self::getContentObject();
    }

    /**
     * @return ConfigurationManagerInterface
     */
    public static function getConfigurationManagerPublic()
    {
        return self::getConfigurationManager();
    }
}
