<?php
namespace In2code\Femanager\Utility;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility as LocalizationUtilityExtbase;

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
 * Class LocalizationUtility
 *
 * @package In2code\Femanager\Utility
 */
class LocalizationUtility extends LocalizationUtilityExtbase
{

    /**
     * Returns the localized label of the LOCAL_LANG key, but prefill extensionName
     *
     * @param string $key The key from the LOCAL_LANG array for which to return the value.
     * @param string $extensionName The name of the extension
     * @param array $arguments the arguments of the extension, being passed over to vsprintf
     * @return string|null
     */
    public static function translate($key, $extensionName = 'femanager', $arguments = null)
    {
        return parent::translate($key, $extensionName, $arguments);
    }

    /**
     * Get locallang translation with key prefix tx_femanager_domain_model_log.state.
     *
     * @param int $state
     * @return null|string
     */
    public static function translateByState($state)
    {
        return self::translate('tx_femanager_domain_model_log.state.' . $state);
    }
}
