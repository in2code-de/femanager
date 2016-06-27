<?php
namespace In2code\Femanager\Utility;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Reflection\Exception\PropertyNotAccessibleException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

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
 * Class ObjectUtility
 *
 * @package In2code\Femanager\Utility
 */
class ObjectUtility extends AbstractUtility
{

    /**
     * Checks if object was changed or not
     *
     * @param object $object
     * @return bool
     */
    public static function isDirtyObject($object)
    {
        foreach (array_keys($object->_getProperties()) as $propertyName) {
            try {
                $property = ObjectAccess::getProperty($object, $propertyName);
            } catch (PropertyNotAccessibleException $e) {
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
            } else {
                /**
                 * ObjectStorage
                 */
                if ($property->_isDirty()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Implode subjobjects on a property (example for usergroups: "ug1, ug2, ug3")
     *
     * @param ObjectStorage $objectStorage
     * @param string $property
     * @param string $glue
     * @return string
     */
    public static function implodeObjectStorageOnProperty($objectStorage, $property = 'uid', $glue = ', ')
    {
        $value = '';
        foreach ($objectStorage as $object) {
            if (method_exists($object, 'get' . ucfirst($property))) {
                $value .= $object->{'get' . ucfirst($property)}();
                $value .= $glue;
            }
        }
        return substr($value, 0, (strlen($glue) * -1));
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    public static function getObjectManager()
    {
        return parent::getObjectManager();
    }
}
