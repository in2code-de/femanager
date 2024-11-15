<?php

declare(strict_types=1);

namespace In2code\Femanager\Utility;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Model\UserGroup;
use Psr\Http\Message\RequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\ArrayUtility;
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
        'tx_femanager_pi2',
        'tx_femanager_registration',
        'tx_femanager_edit',
    ];

    /**
     * Get current pid
     */
    public static function getCurrentPid(): int
    {
        return (int)self::getTypoScriptFrontendController()->id;
    }

    /**
     * Get frontend language uid
     */
    public static function getFrontendLanguageUid(): int
    {
        $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
        return $languageAspect->getId();
    }

    public static function getCharset(): string
    {
        return 'utf-8';
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getUriToCurrentPage(): string
    {
        $contentObject = ObjectUtility::getContentObject();
        $configuration = [
            'parameter' => self::getCurrentPid(),
        ];
        return $contentObject->typoLink_URL($configuration);
    }

    /**
     * Set object properties from forceValues in TypoScript
     *
     * @return User $object
     * @codeCoverageIgnore
     */
    public static function forceValues(User $user, array $settings): User
    {
        foreach ($settings as $field => $config) {
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
     */
    public static function forceValue(User $user, string $field, mixed $value): void
    {
        if ($field === 'usergroup') {
            // need objectstorage for usergroup field
            $user->removeAllUsergroups();
            $values = GeneralUtility::trimExplode(',', $value, true);
            $userGroupRepository = self::getUserGroupRepository();

            foreach ($values as $usergroupUid) {
                /** @var UserGroup $usergroup */
                $usergroup = $userGroupRepository->findByUid((int)$usergroupUid);
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

    public static function getControllerName(RequestInterface $request): string
    {
        $controllerName = '';
        foreach (self::$pluginNames as $pluginName) {
            $variables = $request->getQueryParams()[$pluginName] ?? [];
            ArrayUtility::mergeRecursiveWithOverrule($variables, $request->getParsedBody()[$pluginName] ?? []);
            if (!empty($variables['controller'])) {
                $controllerName = $variables['controller'];
            }
        }

        return $controllerName;
    }

    public static function getActionName(RequestInterface $request): string
    {
        $actionName = '';
        foreach (self::$pluginNames as $pluginName) {
            $variables = $request->getQueryParams()[$pluginName] ?? [];
            ArrayUtility::mergeRecursiveWithOverrule($variables, $request->getParsedBody()[$pluginName] ?? []);
            if (!empty($variables['action'])) {
                $actionName = $variables['action'];
            }
        }

        return $actionName;
    }
}
