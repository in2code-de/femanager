<?php

declare(strict_types=1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Validator\ClientsideValidator;
use In2code\Femanager\Event\ImpersonateEvent;
use In2code\Femanager\Utility\BackendUserUtility;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\UserUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Error\Http\UnauthorizedException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class UserController
 */
class UserController extends AbstractFrontendController
{
    public function listAction(array $filter = []): ResponseInterface
    {
        $this->view->assignMultiple(
            [
                'users' => $this->userRepository->findByUsergroups(
                    $this->settings['list']['usergroup'] ?? '',
                    $this->settings,
                    $filter
                ),
                'filter' => $filter
            ]
        );
        $this->assignForAll();
        return $this->htmlResponse();
    }

    /**
     * Enforce user setting from FlexForm and ignore &tx_femanager_pi1[user]=421
     */
    public function initializeShowAction()
    {
        $arguments = $this->request->getArguments();
        if (!empty($this->settings['show']['user']) ?? '') {
            unset($arguments['user']);
        }
        $this->request->setArguments($arguments);
    }

    public function showAction(User $user = null): ResponseInterface
    {
        $this->view->assign('user', $this->getUser($user));
        $this->assignForAll();
        return $this->htmlResponse();
    }

    /**
     * @throws \Exception
     */
    public function imageDeleteAction(User $user)
    {
        if (UserUtility::getCurrentUser() !== $user) {
            throw new UnauthorizedException('You are not allowed to delete this image', 1516373759972);
        }
        $user->setImage($this->objectManager->get(ObjectStorage::class));
        $this->userRepository->update($user);
        $this->logUtility->log(Log::STATUS_PROFILEUPDATEIMAGEDELETE, $user);
        $this->addFlashMessage(LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEIMAGEDELETE));
        $this->redirectToUri(FrontendUtility::getUriToCurrentPage());
    }

    public function validateAction(): ResponseInterface {
        $requestBody = $this->request->getParsedBody();
        $validation = $requestBody['tx_femanager_validation']['validation'] ?? '';
        $value =  $requestBody['tx_femanager_validation']['value'] ?? '';
        $field =  $requestBody['tx_femanager_validation']['field'] ?? '';
        // TODO: string
        $user =  $requestBody['tx_femanager_validation']['user'] ?? null;
        $additionalValue =  $requestBody['tx_femanager_validation']['additionalValue'] ?? '';
        $plugin =  (int)$requestBody['tx_femanager_validation']['plugin'] ?? 0;
        $pluginName =  $requestBody['tx_femanager_validation']['pluginName'] ?? '';
        $referrerAction = $requestBody['tx_femanager_validation']['referrerAction'] ?? '';

        $clientsideValidator = GeneralUtility::makeInstance(ClientsideValidator::class);
        $result = $clientsideValidator
            ->setValidationSettingsString($validation)
            ->setValue($value)
            ->setFieldName($field)
            ->setUser(null)
            ->setAdditionalValue($additionalValue)
            ->setPlugin($plugin)
            ->setPluginName($pluginName)
            ->setActionName($referrerAction)
            ->validateField($pluginName);

        $this->view->assignMultiple(
            [
                'isValid' => $result,
                'messages' => $clientsideValidator->getMessages(),
                'validation' => $validation,
                'value' => $value,
                'fieldname' => $field,
                'user' => $user
            ]
        );
        return $this->jsonResponse();
    }

    /**
     * Simulate frontenduser login for backend adminstrators only
     *
     * @throws UnauthorizedException
     */
    public function loginAsAction(User $user)
    {
        $this->eventDispatcher->dispatch(new ImpersonateEvent($user));

        if (!BackendUserUtility::isAdminAuthentication()) {
            throw new UnauthorizedException(LocalizationUtility::translate('error_not_authorized'), 1516373787864);
        }
        UserUtility::login($user);
        $this->redirectByAction('loginAs', 'redirect');
        $this->redirectToUri('/');
    }

    /**
     * @return User
     */
    protected function getUser(User $user = null)
    {
        if ($user === null) {
            if (is_numeric($this->settings['show']['user'])) {
                $user = $this->userRepository->findByUid($this->settings['show']['user']);
            } elseif ($this->settings['show']['user'] === '[this]') {
                $user = $this->user;
            }
        }
        return $user;
    }
}
