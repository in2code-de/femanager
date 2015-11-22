<?php
namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Set javascript validation data for input fields
 *
 * @package TYPO3
 * @subpackage Fluid
 */
class FormValidationDataViewHelper extends AbstractViewHelper
{

    /**
     * Set javascript validation data for input fields
     *
     * @param array $settings TypoScript
     * @param string $fieldName Fieldname
     * @param array $additionalAttributes AdditionalAttributes
     * @return array
     */
    public function render($settings, $fieldName, $additionalAttributes = array())
    {
        $controllerName = strtolower($this->controllerContext->getRequest()->getControllerName());
        if ($settings[$controllerName]['validation']['_enable']['client'] === '1') {
            $validationString = $this->getValidationString($settings, $fieldName, $controllerName);
            if (!empty($validationString)) {
                if (!empty($additionalAttributes['data-validation'])) {
                    $additionalAttributes['data-validation'] .= ',' . $validationString;
                } else {
                    $additionalAttributes['data-validation'] = $validationString;
                }
            }
        }
        return $additionalAttributes;
    }

    /**
     * Get validation string like
     *        required, email, min(10), max(10), intOnly,
     *        lettersOnly, uniqueInPage, uniqueInDb, date,
     *        mustInclude(number|letter|special), inList(1|2|3)
     *
     * @param array $settings Validation TypoScript
     * @param string $fieldName Fieldname
     * @param string $controllerName "new", "edit", "invitation"
     * @return string
     */
    protected function getValidationString($settings, $fieldName, $controllerName)
    {
        $string = '';
        foreach ((array) $settings[$controllerName]['validation'][$fieldName] as $validation => $configuration) {
            if (!empty($string)) {
                $string .= ',';
            }
            $string .= $this->getSingleValidationString($validation, $configuration);
        }
        return $string;
    }

    /**
     * @param string $validation
     * @param string $configuration
     * @return string
     */
    protected function getSingleValidationString($validation, $configuration)
    {
        $string = '';
        if ($this->getSingleValidationMode($validation) === 'easy' && $configuration === '1') {
            $string = $validation;
        }
        if ($this->getSingleValidationMode($validation) === 'extended') {
            $string = $validation;
            $string .= '(' . str_replace(',', '|', $configuration) . ')';
        }
        return $string;
    }

    /**
     * @param string $validation
     * @return string
     */
    protected function getSingleValidationMode($validation)
    {
        switch ($validation) {
            case 'min':
                // or
            case 'max':
                // or
            case 'mustInclude':
                // or
            case 'inList':
                $mode = 'extended';
                break;
            default:
                $mode = 'easy';
        }
        return $mode;
    }
}
