<?php
declare(strict_types=1);

namespace In2code\Femanager\DataProcessor;

use In2code\Femanager\Domain\Service\FileService;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\FileUtility;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\ObjectUtility;
use In2code\Femanager\Utility\StringUtility;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ImageManipulation
 */
class ImageManipulation extends AbstractDataProcessor
{

    /**
     * @param array $arguments
     * @return array
     */
    public function process(array $arguments): array
    {
        $this->allowImageProperties();
        foreach ($this->getPropertiesForUpload() as $property) {
            if ($this->isFileIdentifierGiven($arguments, $property) || $this->isUploadError($arguments, $property)) {
                unset($arguments['user'][$property]);
            } else {
                // file upload given
                foreach ((array)$arguments['user'][$property] as $fileItem) {
                    /** @noinspection PhpMethodParametersCountMismatchInspection */
                    $fileService = ObjectUtility::getObjectManager()->get(
                        FileService::class,
                        $this->getNewImageName($fileItem, $property),
                        $fileItem
                    );
                    if ($fileService->isEverythingValid()) {
                        $file = $this->upload($fileItem);
                        $identifier = $this->createSysFileRelation($file->getUid());
                        $arguments['user'][$property] = [$identifier];
                    }
                }
            }
        }
        return $arguments;
    }

    /**
     * @param int $fileIdentifier
     * @return int
     */
    protected function createSysFileRelation(int $fileIdentifier): int
    {
        $properties = [
            'pid' => FrontendUtility::getCurrentPid(),
            'uid_local' => $fileIdentifier,
            'tstamp' => time(),
            'crdate' => time()
        ];
        foreach ($this->getConfiguration('sysFileRelation') as $field => $value) {
            $properties[$field] = $value;
        }

        $databaseConnectionForPages = ObjectUtility::getConnectionPool()->getConnectionForTable('sys_file_reference');
        $databaseConnectionForPages->insert(
            'sys_file_reference',
            $properties
        );

        return (int)$databaseConnectionForPages->lastInsertId('sys_file_reference');
    }

    /**
     * @param array $fileItem
     * @return File
     * @throws \Exception
     */
    protected function upload(array $fileItem): File
    {
        $uploadFolder = $this->getUploadFolder();
        $uploadedFile = $uploadFolder->addFile($fileItem['tmp_name'],
            $this->getNewImageName($fileItem), DuplicationBehavior::RENAME);
        return $uploadedFile;
    }

    /**
     * @return array
     */
    protected function getPropertiesForUpload(): array
    {
        $propertylist = $this->getConfiguration('propertyNamesForUpload');
        return GeneralUtility::trimExplode(',', $propertylist, true);
    }

    /**
     * @param array $fileItem
     * @return string
     */
    protected function getNewImageName(array $fileItem): string
    {
        $imageName = '';
        if (!empty($fileItem['name'])) {
            $imageName = $fileItem['name'];
        }
        $imageName = StringUtility::cleanString($imageName);
        return $imageName;
    }

    /**
     * @param bool $absolute
     * @return Folder
     */
    protected function getUploadFolder(bool $absolute = true): Folder
    {
        $resourceFactory = ResourceFactory::getInstance();
        $uploadFolderIdentifier = (string)ConfigurationUtility::getConfiguration('misc.uploadFolder');
        if (StringUtility::startsWith($uploadFolderIdentifier, 'fileadmin')) {
            // Fall back to legacy configuration without fal usage, create fal-identifer for fileadmin path
            $fileUtility = ObjectUtility::getObjectManager()->get(FileUtility::class);
            $uploadFolderIdentifier = $fileUtility->substituteFileadminFromPathAndName($uploadFolderIdentifier);
        }

        try {
            return $resourceFactory->getFolderObjectFromCombinedIdentifier($uploadFolderIdentifier);
        } catch (FolderDoesNotExistException $e) {
            $storage = $resourceFactory->getStorageObjectFromCombinedIdentifier($uploadFolderIdentifier);
            list($storageId, $folderPath) = GeneralUtility::trimExplode(':', $uploadFolderIdentifier);
            $storage->createFolder($folderPath);
            return $resourceFactory->getFolderObjectFromCombinedIdentifier($uploadFolderIdentifier);
        }
    }

    /**
     * @return void
     */
    protected function allowImageProperties()
    {
        if (!empty($this->controllerArguments['user'])) {
            $this->controllerArguments['user']->getPropertyMappingConfiguration()->forProperty(
                'image'
            )->allowProperties(0);
        }
    }

    /**
     * @param array $arguments
     * @param $property
     * @return bool
     */
    protected function isFileIdentifierGiven(array $arguments, $property): bool
    {
        return !empty($arguments['user'][$property][0]['__identity']);
    }

    /**
     * @param array $arguments
     * @param $property
     * @return bool
     */
    protected function isUploadError(array $arguments, $property): bool
    {
        return !empty($arguments['user'][$property][0]['error']);
    }
}
