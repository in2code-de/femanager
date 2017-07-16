<?php
declare(strict_types=1);
namespace In2code\Femanager\DataProcessor;

use In2code\Femanager\Domain\Service\FileService;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\FileUtility;
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
        foreach ($this->getPropertiesForUpload() as $property) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $fileService = ObjectUtility::getObjectManager()->get(
                FileService::class,
                $this->getNewImageName($arguments, $property),
                $arguments['user'][$property]
            );
            if ($fileService->isEverythingValid()) {
                FileUtility::createFolderIfNotExists($this->getUploadFolder());
                $arguments['user'][$property] = $this->upload($arguments, $property);
            }
        }

        return $arguments;
    }

    /**
     * @param array $arguments
     * @param string $property
     * @return string New filename (without path)
     * @throws \Exception
     */
    protected function upload(array $arguments, string $property): string
    {
        $basicFileFunctions = ObjectUtility::getObjectManager()->get(BasicFileUtility::class);
        $uniqueFileName = $basicFileFunctions->getUniqueName(
            $this->getNewImageName($arguments, $property),
            $this->getUploadFolder()
        );
        if (GeneralUtility::upload_copy_move($arguments['user'][$property]['tmp_name'], $uniqueFileName)) {
            $fileInfo = pathinfo($uniqueFileName);
            return $fileInfo['basename'];
        } else {
            throw new \Exception('File for property "' . $property . '" could not be uploaded!');
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
     * @param array $arguments
     * @param string $property
     * @return string
     */
    protected function getNewImageName(array $arguments, string $property): string
    {
        $imageName = '';
        if (!empty($arguments['user'][$property]['name'])) {
            $imageName = $arguments['user'][$property]['name'];
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
}
