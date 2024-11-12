<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class RequestViewHelper
 */
class RequestViewHelper extends AbstractViewHelper
{
    /**
     * @var array|string
     */
    protected $variable = [];

    /**
     * @var int
     */
    protected $depth = 1;

    /**
     * @var array
     */
    protected $testVariables;

    /**
     * Get a GET or POST parameter
     */
    public function render(): string
    {
        $parameter = $this->arguments['parameter'];
        $htmlspecialchars = $this->arguments['htmlspecialchars'];

        $parts = $this->init($parameter);
        $result = $this->getVariableFromDepth($parts);
        if ($htmlspecialchars === true && is_string($result)) {
            $result = htmlspecialchars($result);
        }

        if (is_array($result) || $result === null) {
            // ensure that the return value is always as string
            return '';
        }

        return $result;
    }

    protected function getVariableFromDepth(?array $param): array|string|null
    {
        if (is_array($this->variable)) {
            $this->variable = $this->variable[$param[$this->depth]] ?? null;
            $this->depth++;
            $this->getVariableFromDepth($param);
        }

        return $this->variable;
    }

    /**
     * Initially sets $this->variable
     *
     * @param $parameter
     */
    protected function init($parameter): array
    {
        $parts = explode('|', (string)$parameter);
        $this->variable = $GLOBALS['TYPO3_REQUEST']->getParsedBody()[$parts[0]] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()[$parts[0]] ?? null;
        if ($this->testVariables) {
            $this->variable = $this->testVariables[$parts[0]];
        }

        return $parts;
    }

    /**
     * Register all arguments for this viewhelper
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('parameter', 'string', 'like tx_ext_pi1|list|field', false, '');
        $this->registerArgument('htmlspecialchars', 'bool', 'Enable/Disable htmlspecialchars', false, true);
    }
}
