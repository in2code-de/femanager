<?php
declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Validation;

use In2code\Femanager\Domain\Service\ValidationSettingsService;
use In2code\Femanager\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FormValidationDataViewHelper
 */
class FormValidationDataViewHelper extends AbstractValidationViewHelper
{

    /**
     * Set javascript validation data for input fields
     *
     * @param string $fieldName Fieldname
     * @param array $settings TypoScript
     * @param array $additionalAttributes AdditionalAttributes
     * @return array
     */
    public function render($fieldName, $settings = [], $additionalAttributes = [])
    {
        if ($settings !== []) {
            GeneralUtility::deprecationLog(
                'Settings should not be filled any more in field partials. Pls update your femanager partial files.'
            );
        }
        $validationService = ObjectUtility::getObjectManager()->get(
            ValidationSettingsService::class,
            $this->getControllerName(),
            $this->getValidationName()
        );
        if ($validationService->isValidationEnabled('client')) {
            $validationString = $validationService->getValidationStringForField($fieldName);
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
}
