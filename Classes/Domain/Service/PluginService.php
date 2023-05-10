<?php

declare(strict_types=1);
namespace In2code\Femanager\Domain\Service;

class PluginService
{
    final public const ALLOWED_PLUGINS = [
        'tx_femanager_registration',
        'tx_femanager_edit',
        'tx_femanager_list',
        'tx_femanager_detail',
        'tx_femanager_invitation',
        'tx_femanager_resendConfirmationMail'
    ];

    /**
     * @return string The name of the femanager plugin in this request
     */
    public function getFemanagerPluginNameFromRequest(): string
    {
        $request =  $GLOBALS['TYPO3_REQUEST'];

        // only the name of the first femanager plugin is returned
        if (is_array($request->getParsedBody())) {
            foreach ($request->getParsedBody() as $key => $value) {
                if (in_array($key, self::ALLOWED_PLUGINS)) {
                    return $key;
                }
            }
        }
        return '';
    }

    public function getAllowedPlugins(): array
    {
        return self::ALLOWED_PLUGINS;
    }
}
