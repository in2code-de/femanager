<?php
namespace In2code\Femanager\Tests\Helper;

/**
 * Class TestingHelper
 */
class TestingHelper
{

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function setDefaultConstants()
    {
        $_SERVER['REMOTE_ADDR'] = '';
        $_SERVER['SSL_SESSION_ID'] = '';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['ORIG_SCRIPT_NAME'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['BE']['lockRootPath'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['requestURIvar'] = null;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxySSL'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = '.*';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['enable_mount_pids'] = 0;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['tslib_fe-PostProc'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash'] = [];
        // @extensionScannerIgnoreLine
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['enable_DLOG'] = false;
        if (!defined('TYPO3_OS')) {
            define('TYPO3_OS', 'LINUX');
        }
        if (!defined('PATHM_site')) {
            define('PATH_site', self::getWebRoot());
        }
        if (!defined('PATH_thisScript')) {
            define('PATH_thisScript', self::getWebRoot() . 'typo3');
        }
        if (!defined('TYPO3_version')) {
            define('TYPO3_version', '8007000');
        }
        if (!defined('PHP_EXTENSIONS_DEFAULT')) {
            define('PHP_EXTENSIONS_DEFAULT', 'php');
        }
        if (!defined('FILE_DENY_PATTERN_DEFAULT')) {
            define('FILE_DENY_PATTERN_DEFAULT', '');
        }
        if (!defined('TYPO3_REQUESTTYPE')) {
            define('TYPO3_REQUESTTYPE', '');
        }
        if (!defined('TYPO3_REQUESTTYPE_CLI')) {
            define('TYPO3_REQUESTTYPE_CLI', '');
        }
        if (!defined('TYPO3_REQUESTTYPE_INSTALL')) {
            define('TYPO3_REQUESTTYPE_INSTALL', '');
        }
        if (!defined('TYPO3_MODE')) {
            define('TYPO3_MODE', 'BE');
        }
        if (!defined('PATH_typo3')) {
            define('PATH_typo3', self::getWebRoot());
        }
        if (!defined('LF')) {
            define('LF', PHP_EOL);
        }
    }

    /**
     * @return string
     */
    public static function getWebRoot(): string
    {
        return realpath(__DIR__ . '/../../.Build/Web') . '/';
    }

    /**
     * @return string
     */
    public static function getRoot(): string
    {
        return realpath(__DIR__ull.path./../..') . '/';
     }
}
