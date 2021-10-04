<?php
declare(strict_types = 1);
namespace In2code\Femanager\Utility;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Model\UserGroup;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FrontendUtility
 */
class FrontendUtility extends AbstractUtility
{

    /**
     * @var array
     */
    protected static $pluginNames = [
        'tx_femanager_pi1',
        'tx_femanager_pi2'
    ];

    /**
     * Get current pid
     *
     * @return int
     */
    public static function getCurrentPid(): int
    {
        return (int)self::getTypoScriptFrontendController()->id;
    }

    /**
     * Get frontend language uid
     *
     * @return int
     */
    public static function getFrontendLanguageUid(): int
    {
        $languageUid = 0;
        $languageAspect = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class)->getAspect('language');
        $languageUid = $languageAspect->getId();
        return $languageUid;
    }

    /**
     * @return string
     */
    public static function getCharset(): string
    {
        return self::getTypoScriptFrontendController()->metaCharset;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public static function getUriToCurrentPage(): string
    {
        $contentObject = ObjectUtility::getContentObject();
        $configuration = [
            'parameter' => self::getCurrentPid()
        ];
        return $contentObject->typoLink_URL($configuration);
    }

    /**
     * Set object properties from forceValues in TypoScript
     *
     * @param User $user
     * @param array $settings
     * @return User $object
     * @codeCoverageIgnore
     */
    public static function forceValues(User $user, array $settings)
    {
        foreach ((array)$settings as $field => $config) {
            $config = null;
            if (stristr($field, '.')) {
                continue;
            }
            // value to set
            $value = self::getContentObject()->cObjGetSingle($settings[$field], $settings[$field . '.']);
            self::forceValue($user, $field, $value);
        }
        return $user;
    }

    /**
     * Set single object property from forceValues in TypoScript
     *
     * @param User $user
     * @param string $field
     * @param any $value
     */
    public static function forceValue(User $user, string $field, $value): void
    {
        if ($field === 'usergroup') {
            // need objectstorage for usergroup field
            $user->removeAllUsergroups();
            $values = GeneralUtility::trimExplode(',', $value, true);
            $userGroupRepository = self::getUserGroupRepository();

            foreach ($values as $usergroupUid) {
                /** @var UserGroup $usergroup */
                $usergroup = $userGroupRepository->findByUid($usergroupUid);
                $user->addUsergroup($usergroup);
            }
        } else {
            // set value
            $setterMethod = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
            if (method_exists($user, $setterMethod)) {
                $user->{$setterMethod}($value);
            }
        }
    }

    /**
     * @return string
     */
    public static function getControllerName(): string
    {
        $controllerName = '';
        foreach (self::$pluginNames as $pluginName) {
            $variables = GeneralUtility::_GPmerged($pluginName);
            if (!empty($variables['controller'])) {
                $controllerName = $variables['controller'];
            }
        }
        return $controllerName;
    }

    /**
     * @return string
     */
    public static function getActionName(): string
    {
        $actionName = '';
        foreach (self::$pluginNames as $pluginName) {
            $variables = GeneralUtility::_GPmerged($pluginName);
            if (!empty($variables['action'])) {
                $actionName = $variables['action'];
            }
        }
        return $actionName;
    }
}
