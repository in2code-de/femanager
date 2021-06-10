<?php
declare(strict_types = 1);
namespace In2code\Femanager\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Class FileUtility
 */
class FileUtility extends AbstractUtility
{

    /**
     * @param string $path Absolute path
     * @throws \Exception
     */
    public static function createFolderIfNotExists(string $path)
    {
        if (!is_dir($path) && !GeneralUtility::mkdir($path)) {
            throw new \UnexpectedValueException(
                'Folder ' . self::getRelativeFolderFromAbsolutePath($path) . ' does not exists and can not be created!',
                1516373962125
            );
        }
    }

    /**
     * Get relative path from absolute path, but don't touch if it's already a relative path
     *
     * @param string $path
     * @return string
     */
    public static function getRelativeFolderFromAbsolutePath(string $path): string
    {
        if (PathUtility::isAbsolutePath($path)) {
            $path = PathUtility::getRelativePathTo($path);
        }
        return $path;
    }
}
