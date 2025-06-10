<?php

namespace In2code\Femanager\Controller;

trait HasConfirmationByFormSubmitTrait
{

    private function addVariablesForActionConfirmation($approvalOfActionByFormSubmitRequired, $user, $status, $hash = null)
    {
        if($this->settings['new']['email']['activateEmailLinkFormConfirmation'] ?? false) {
            if(!$this->request->hasArgument('approve')) {
                $this->view->assignMultiple([
                    'user' => $user,
                    'hash' => $hash,
                    'actionName' => $this->request->getControllerActionName(),
                    'controllerName' => $this->request->getControllerName(),
                    'showApprovalStep' => 1,
                    'status' => $status,
                    'languageKeyText' => "form" . ucfirst($status) . "Text",
                    'languageKeySubmitButton' => "form" . ucfirst($status) . "SubmitButton"]);
                return true;
            }
        }
        return false;
    }

}
