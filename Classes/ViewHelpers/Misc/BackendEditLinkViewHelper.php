<?php
namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * BackendEditLinkViewHelper
 *
 * @package TYPO3
 * @subpackage Fluid
 */
class BackendEditLinkViewHelper extends AbstractViewHelper
{

    /**
     * Create a link for backend edit
     *
     * @param string $tableName
     * @param int $identifier
     * @param bool $addReturnUrl
     * @return string
     */
    public function render($tableName, $identifier, $addReturnUrl = true)
    {
        $uriParameters = [
            'edit' => [
                $tableName => [
                    $identifier => 'edit'
                ]
            ]
        ];
        if ($addReturnUrl) {
            $uriParameters['returnUrl'] =
                BackendUtility::getModuleUrl(GeneralUtility::_GET('M'), $this->getCurrentParameters());
        }
        return BackendUtility::getModuleUrl('record_edit', $uriParameters);
    }

    /**
     * Get all GET/POST params without module name and token
     *
     * @return array
     */
    protected function getCurrentParameters()
    {
        $parameters = [];
        $ignoreKeys = [
            'M',
            'moduleToken'
        ];
        foreach ((array) GeneralUtility::_GET() as $key => $value) {
            if (in_array($key, $ignoreKeys)) {
                continue;
            }
            $parameters[$key] = $value;
        }
        return $parameters;
    }
}
