<?php
namespace In2code\Femanager\Utility;

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
 * Class StringUtility
 *
 * @package In2code\Femanager\Utility
 */
class StringUtility extends AbstractUtility
{

    /**
     * Only allowed a-z, A-Z, 0-9, -, .
     * Others will be replaced
     *
     * @param string $filename
     * @param string $replace
     * @return string
     */
    public static function cleanString($filename, $replace = '_')
    {
        return preg_replace('/[^a-zA-Z0-9-\.]/', $replace, trim($filename));
    }

    /**
     * Read values between brackets
     *
     *      test(1,2,3) => 1,2,3
     *
     * @param string $value
     * @return string
     */
    public static function getValuesInBrackets($value)
    {
        preg_match_all('/\(.*?\)/i', $value, $result);
        return str_replace(['(', ')'], '', $result[0][0]);
    }

    /**
     * Read values before brackets
     *
     *      test(1,2,3) => test
     *
     * @param string $value
     * @return string
     */
    public static function getValuesBeforeBrackets($value)
    {
        $valueParts = GeneralUtility::trimExplode('(', $value, true);
        return $valueParts[0];
    }

    /**
     * Check if string starts with another string
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return stristr($haystack, $needle) && strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    /**
     * Check if string ends with another string
     *
     * @param string $haystack
     * @param string $needle
     * @return string
     */
    public static function endsWith($haystack, $needle)
    {
        return stristr($haystack, $needle) && strlen($haystack) - strlen($needle) === strpos($haystack, $needle);
    }

    /**
     * Create array for swiftmailer
     *        sender and receiver mail/name combination with fallback
     *
     * @param string $emailString String with separated emails (splitted by \n)
     * @param string $name Name for every email name combination
     * @return array $mailArray
     */
    public static function makeEmailArray($emailString, $name = 'femanager')
    {
        $emails = GeneralUtility::trimExplode(PHP_EOL, $emailString, true);
        $mailArray = [];
        foreach ($emails as $email) {
            if (GeneralUtility::validEmail($email)) {
                $mailArray[$email] = $name;
            }
        }
        return $mailArray;
    }

    /**
     * createRandomFileName
     *
     * @param int $length
     * @param bool $addUpperCase
     * @param bool $addSpecialCharacters
     * @return string
     */
    public static function getRandomString($length = 32, $addUpperCase = true, $addSpecialCharacters = true)
    {
        $characters = self::getNumbersString() . self::getCharactersString();
        if ($addUpperCase) {
            $characters .= self::getUpperCharactersString();
        }
        if ($addSpecialCharacters) {
            $characters .= '#+*&%$§()[]{}!.:-_,;';
        }
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $key = mt_rand(0, strlen($characters) - 1);
            $string .= $characters[$key];
        }
        return $string;
    }

    /**
     * @return string "0123456789"
     */
    public static function getNumbersString()
    {
        return implode('', range(0, 9));
    }

    /**
     * @return string "abcdefghijklmnopqrstuvwxyz"
     */
    public static function getCharactersString()
    {
        return implode('', range('a', 'z'));
    }

    /**
     * @return string "ABCDEFGHIJKLMNOPQRSTUVWXYZ"
     */
    public static function getUpperCharactersString()
    {
        return implode('', range('A', 'Z'));
    }

    /**
     * @param string $string
     * @return string
     */
    public static function removeDoubleSlashes($string)
    {
        return str_replace('//', '/', $string);
    }
}
