<?php

declare(strict_types=1);
namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\Security\FileNameValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

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
    protected $fallbackExtensions = 'jpg,jpeg,png,gif,bmp,tif,tiff';

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
     * @todo add filesize check
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
    protected function validFileExtension(): bool
    {
        $extensionList = ConfigurationUtility::getConfiguration('misc.uploadFileExtension');
        if (!empty($extensionList)) {
            $extensionList = str_replace(' ', '', (string) $extensionList);
        } else {
            $extensionList = $this->fallbackExtensions;
        }
        $fileInfo = pathinfo($this->fileName);

        return !empty($fileInfo['extension']) &&
            GeneralUtility::inList($extensionList, strtolower($fileInfo['extension'])) &&
            GeneralUtility::makeInstance(FileNameValidator::class)->isValid($this->fileName) &&
            GeneralUtility::validPathStr($this->fileName);
    }

    /**
     * Create sys_file entry for given filename and return uid
     *
     * @param string $file absolute path and filename
     */
    public function indexFile(string $file): int
    {
        $fileIdentifier = 0;
        if (file_exists($file)) {
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
            $file = $resourceFactory->getFileObjectFromCombinedIdentifier($this->getCombinedIdentifier($file));
            $fileIdentifier = (int)$file->getProperty('uid');
        }
        return $fileIdentifier;
    }

    /**
     * build combined identifier from absolute filename:
     *      "/var/www/fileadmin/folder/test.pdf" => "1:folder/test.pdf"
     *
     * @param string $file relative path and filename
     */
    protected function getCombinedIdentifier(string $file): string
    {
        //  @Todo Make it a bit less ugly
        $file = PathUtility::getRelativePathTo($file);
        $identifier = $this->substituteFileadminFromPathAndName($file);
        return '1:' . $identifier;
    }

    /**
     * "fileadmin/downloads/test.pdf" => "/downloads/test.pdf"
     */
    protected function substituteFileadminFromPathAndName(string $pathAndName): string
    {
        $substituteString = 'fileadmin/';
        if (str_starts_with($pathAndName, $substituteString)) {
            $pathAndName = str_replace($substituteString, '', $pathAndName);
        }
        if (!str_starts_with($pathAndName, '/')) {
            $pathAndName = '/' . $pathAndName;
        }
        return $pathAndName;
    }
}
