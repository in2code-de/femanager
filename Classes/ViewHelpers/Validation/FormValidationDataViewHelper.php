<?php
declare(strict_types = 1);
namespace In2code\Femanager\ViewHelpers\Validation;

use In2code\Femanager\Domain\Service\ValidationSettingsService;
use In2code\Femanager\Utility\ObjectUtility;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class FormValidationDataViewHelper
 */
class FormValidationDataViewHelper extends AbstractValidationViewHelper implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Set javascript validation data for input fields
     *
     * @return array
     */
    public function render()
    {
        $settings = $this->arguments['settings'];
        $fieldName = $this->arguments['fieldName'];
        $additionalAttributes = $this->arguments['additionalAttributes'];

        if ($settings !== []) {
            $this->logger->debug(
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

    /**
     * Initialize the arguments.
     *
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('settings', 'array ', 'all TypoScript settings for this form', true);
        $this->registerArgument('fieldName', 'string ', 'Fieldname which should get validated', true);
        $this->registerArgument('additionalAttributes', 'array ', '$additionalAttributes for this field', false);
    }
}
