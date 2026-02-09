<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Form;

use In2code\Femanager\Utility\TypoScriptUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as FluidViewHelperException;

class TextfieldViewHelper extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    public function __construct(protected readonly TypoScriptUtility $typoScriptUtility)
    {
        parent::__construct();
    }

    /**
     * Initialize the arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this ViewHelper', false, 'f3-form-error');
        $this->registerArgument('required', 'bool', 'If the field is required or not', false, false);
        $this->registerArgument('type', 'string', 'The field type, e.g. "text", "email", "url" etc.', false, 'text');
    }

    public function render(): string
    {
        $required = $this->arguments['required'];
        $type = $this->arguments['type'];

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
        $request = $this->getRequest();
        $controllerName = strtolower($request->getControllerName());
        $contentObject = $request->getAttribute('currentContentObject');
        $typoScript = $this->typoScriptUtility->getTypoScript();
        $prefillTypoScript =
            $typoScript['plugin.']['tx_femanager.']['settings.'][$controllerName . '.']['prefill.'] ?? [];
        return $contentObject->cObjGetSingle(
            $prefillTypoScript[$this->arguments['property']] ?? '',
            $prefillTypoScript[$this->arguments['property'] . '.'] ?? ''
        );
    }
}
