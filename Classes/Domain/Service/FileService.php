<?php
declare(strict_types=1);
namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FileService
 */
class FileService
{

    /**
     * @var string
     */
    protected $fileName = '';

    /**
     * @var array
     */
    protected $fileArray = [];

    /**
     * @var string
     */
    protected $fallbackExtensions = 'jpg,jpeg,png,gif,bmp,svg,tif,tiff';

    /**
     * FileService constructor.
     *
     * @param string $filename Filename like (upload.png)
     * @param array $fileArray From PHP with tmp_name, etc...
     */
    public function __construct(string $filename, array $fileArray)
    {
        $this->fileName = $filename;
        $this->fileArray = $fileArray;
    }

    /**
     * @return bool
     */
    public function isEverythingValid(): bool
    {
        return $this->validFileExtension();
    }

    /**
     * Check extension of given filename
     *
     * @return bool If Extension is allowed
     */
    public function validFileExtension(): bool
    {
        $extensionList = ConfigurationUtility::getConfiguration('misc.uploadFileExtension');
        if (!empty($extensionList)) {
            $extensionList = str_replace(' ', '', $extensionList);
        } else {
            $extensionList = $this->fallbackExtensions;
        }
        $fileInfo = pathinfo($this->fileName);

        return !empty($fileInfo['extension']) &&
            GeneralUtility::inList($extensionList, strtolower($fileInfo['extension'])) &&
            GeneralUtility::verifyFilenameAgainstDenyPattern($this->fileName) &&
            GeneralUtility::validPathStr($this->fileName);
    }
}
