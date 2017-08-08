<?php
declare(strict_types=1);

namespace In2code\Femanager\DataProcessor;

use In2code\Femanager\Domain\Service\FileService;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\FileUtility;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\ObjectUtility;
use In2code\Femanager\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\File\BasicFileUtility;
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
                        FileUtility::createFolderIfNotExists($this->getUploadFolder());
                        $pathAndFilename = $this->upload($fileItem);
                        $fileIdentifier = $fileService->indexFile($pathAndFilename);
                        $identifier = $this->createSysFileRelation($fileIdentifier);
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
        ObjectUtility::getDatabaseConnection()->exec_INSERTquery('sys_file_reference', $properties);
        return (int)ObjectUtility::getDatabaseConnection()->sql_insert_id();
    }

    /**
     * @param array $fileItem
     * @return string New filename (absolute with path)
     * @throws \Exception
     */
    protected function upload(array $fileItem): string
    {
        $basicFileFunctions = ObjectUtility::getObjectManager()->get(BasicFileUtility::class);
        $uniqueFileName = $basicFileFunctions->getUniqueName(
            $this->getNewImageName($fileItem),
            $this->getUploadFolder()
        );
        if (GeneralUtility::upload_copy_move($fileItem['tmp_name'], $uniqueFileName)) {
            return $uniqueFileName;
        } else {
            throw new \Exception('File "' . $this->getNewImageName($fileItem) . '" could not be uploaded!');
        }
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
     * @return string
     */
    protected function getUploadFolder(bool $absolute = true): string
    {
        $path = (string)ConfigurationUtility::getConfiguration('misc.uploadFolder');
        if ($absolute === true) {
            $path = GeneralUtility::getFileAbsFileName($path);
        }
        return $path;
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
