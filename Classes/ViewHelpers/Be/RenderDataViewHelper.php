<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Be;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Renders an array or JSON string as a structured definition list.
 * Supports nested arrays/objects recursively.
 *
 * Usage:
 *   <femanager:be.renderData data="{log.additionalPropertiesAsArray}" />
 */
class RenderDataViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('data', 'mixed', 'Array or JSON string to render', true);
    }

    public function render(): string
    {
        $data = $this->arguments['data'];

        if (is_string($data) && $data !== '') {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : ['value' => $data];
        }

        if (!is_array($data) || $data === []) {
            return '';
        }

        return $this->renderArray($data);
    }

    private function renderArray(array $data): string
    {
        $html = '<dl class="femanager-log-data">';

        foreach ($data as $key => $value) {
            $html .= '<dt>' . htmlspecialchars((string)$key) . '</dt>';

            if (is_array($value)) {
                $html .= '<dd>' . $this->renderArray($value) . '</dd>';
            } else {
                $html .= '<dd>' . htmlspecialchars((string)$value) . '</dd>';
            }
        }

        $html .= '</dl>';

        return $html;
    }
}