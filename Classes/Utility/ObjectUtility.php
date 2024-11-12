<?php

declare(strict_types=1);

namespace In2code\Femanager\Utility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Reflection\Exception\PropertyNotAccessibleException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class ObjectUtility
 */
class ObjectUtility extends AbstractUtility
{
    public static function getQueryBuilder(string $tableName): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
    }

    protected static function getContentObject(): ContentObjectRenderer
    {
        return GeneralUtility::makeInstance(ContentObjectRenderer::class);
    }

    /**
     * Checks if object was changed or not
     *
     * @param object $object
     * @codeCoverageIgnore
     */
    public static function isDirtyObject($object, RequestInterface $request): bool
    {
        foreach (array_keys($object->_getProperties()) as $propertyName) {
            try {
                $property = ObjectAccess::getProperty($object, $propertyName);
            } catch (PropertyNotAccessibleException) {
                // if property can not be accessed
                continue;
            }

            /**
             * std::Property (string, int, etc..),
             * PHP-Objects (DateTime, RecursiveIterator, etc...),
             * TYPO3-Objects (user, page, etc...)
             */
            if (!$property instanceof ObjectStorage) {
                if ($object->_isDirty($propertyName)) {
                    return true;
                }
            } elseif ($property->_isDirty()) {
                /**
                 * ObjectStorage
                 */
                return true;
            }

            /** check if there is an uploaded image */
            $uploadedFiles = $request->getUploadedFiles();
            if (
                $uploadedFiles !== []
                && !empty($uploadedFiles['image'])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Implode subjobjects on a property (example for usergroups: "ug1, ug2, ug3")
     */
    public static function implodeObjectStorageOnProperty(
        ObjectStorage $objectStorage,
        string $property = 'uid',
        string $glue = ', '
    ): string {
        $values = [];
        foreach ($objectStorage as $object) {
            try {
                $values[] = ObjectAccess::getProperty($object, $property);
            } catch (\Exception $exception) {
                unset($exception);
            }
        }

        return implode($glue, $values);
    }
}
