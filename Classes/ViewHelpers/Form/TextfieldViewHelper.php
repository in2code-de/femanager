<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Form;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as FluidViewHelperException;

/**
 * Class TextfieldViewHelper
 */
class TextfieldViewHelper extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    /**
     * Initialize the arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerTagAttribute(
            'autofocus',
            'string',
            'Specifies that an input should automatically get focus when the page loads'
        );
        $this->registerTagAttribute(
            'disabled',
            'string',
            'Specifies that the input element should be disabled when the page loads'
        );
        $this->registerTagAttribute(
            'maxlength',
            'int',
            'The maxlength attribute of the input field (will not be validated)'
        );
        $this->registerTagAttribute(
            'readonly',
            'string',
            'The readonly attribute of the input field'
        );
        $this->registerTagAttribute(
            'size',
            'int',
            'The size of the input field'
        );
        $this->registerTagAttribute(
            'placeholder',
            'string',
            'The placeholder of the textfield'
        );
        $this->registerTagAttribute(
            'pattern',
            'string',
            'HTML5 validation pattern'
        );
        $this->registerArgument(
            'errorClass',
            'string',
            'CSS class to set if there are errors for this ViewHelper',
            false,
            'f3-form-error'
        );
        $this->registerUniversalTagAttributes();
        $this->registerArgument(
            'required',
            'bool',
            'If the field is required or not',
            false,
            false
        );
        $this->registerArgument(
            'type',
            'string',
            'The field type, e.g. "text", "email", "url" etc.',
            false,
            'text'
        );
    }

    /**
     * Renders the textfield.
     *
     * @return string
     */
    public function render()
    {
        $required = $this->arguments['required'] ?? false;
        $type = $this->arguments['type'] ?? null;

        $name = $this->getName();
        $this->registerFieldNameForFormTokenGeneration($name);
        $this->setRespectSubmittedDataValue(true);

        $this->tag->addAttribute('type', $type);
        $this->tag->addAttribute('name', $name);

        $value = $this->getValueAttribute();

        // prefill value from TypoScript
        if (empty($value) && $this->getValueFromTypoScript()) {
            $value = $this->getValueFromTypoScript();
        }

        if ($value !== null) {
            $this->tag->addAttribute('value', $value);
        }

        if ($required !== false) {
            $this->tag->addAttribute('required', 'required');
        }

        $this->addAdditionalIdentityPropertiesIfNeeded();
        $this->setErrorClassAttribute();

        return $this->tag->render();
    }
    /**
     * Read value from TypoScript
     *
     * @return string Value from TypoScript
     * @throws FluidViewHelperException
     */
    protected function getValueFromTypoScript(): string
    {
        if (!$this->renderingContext instanceof RenderingContext) {
            throw new FluidViewHelperException(
                'Something went wrong; RenderingContext should be available in ViewHelper',
                1638341334
            );
        }
        $controllerName = strtolower((string)$this->renderingContext->getRequest()->getControllerName());
        $contentObject = $this->configurationManager->getContentObject();
        $typoScript = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $prefillTypoScript =
            $typoScript['plugin.']['tx_femanager.']['settings.'][$controllerName . '.']['prefill.'] ?? 0;
        $value = $contentObject->cObjGetSingle(
            $prefillTypoScript[$this->arguments['property']] ?? '',
            $prefillTypoScript[$this->arguments['property'] . '.'] ?? ''
        );
        return $value;
    }
}
