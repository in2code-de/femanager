<?php
namespace In2code\Femanager\Utility;

use TYPO3\CMS\Core\Utility\File\BasicFileUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 in2code.de
 *  Alex Kellner <alexander.kellner@in2code.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class FileUtility
 *
 * @package In2code\Femanager\Utility
 */
class FileUtility extends AbstractUtility
{

    /**
     * Upload file from $_FILES['qqfile']
     *
     * @return string|bool false or filename like "file.png"
     */
    public static function uploadFile()
    {
        $files = self::getFilesArray();
        if (!is_array($files['qqfile'])) {
            return false;
        }
        if (empty($files['qqfile']['name']) || !self::checkExtension($files['qqfile']['name'])) {
            return false;
        }

        // create new filename and upload it
        $basicFileFunctions = self::getObjectManager()->get(BasicFileUtility::class);
        $filename = StringUtility::cleanString($files['qqfile']['name']);
        $newFile = $basicFileFunctions->getUniqueName(
            $filename,
            GeneralUtility::getFileAbsFileName(self::getUploadFolderFromTca())
        );
        if (GeneralUtility::upload_copy_move($files['qqfile']['tmp_name'], $newFile)) {
            $fileInfo = pathinfo($newFile);
            return $fileInfo['basename'];
        }

        return false;
    }

    /**
     * Check extension of given filename
     *
     * @param string $filename Filename like (upload.png)
     * @return bool If Extension is allowed
     */
    public static function checkExtension($filename)
    {
        $extensionList = 'jpg,jpeg,png,gif,bmp';
        $settings = self::getTypoScriptFrontendController()->tmpl->setup['plugin.']['tx_femanager.']['settings.'];
        if (!empty($settings['misc.']['uploadFileExtension'])) {
            $extensionList = $settings['misc.']['uploadFileExtension'];
            $extensionList = str_replace(' ', '', $extensionList);
        }
        $fileInfo = pathinfo($filename);

        return !empty($fileInfo['extension']) &&
            GeneralUtility::inList($extensionList, strtolower($fileInfo['extension'])) &&
            GeneralUtility::verifyFilenameAgainstDenyPattern($filename) &&
            GeneralUtility::validPathStr($filename);
    }

    /**
     * Read fe_users image uploadfolder from TCA
     *
     * @return string path - standard "uploads/pics"
     */
    public static function getUploadFolderFromTca()
    {
        $tca = self::getTcaFromTable();
        $path = $tca['columns']['image']['config']['uploadfolder'];
        if (empty($path)) {
            $path = 'uploads/pics';
        }
        return $path;
    }
}
