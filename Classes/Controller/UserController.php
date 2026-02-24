<?php

declare(strict_types=1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Validator\ClientsideValidator;
use In2code\Femanager\Event\ImpersonateEvent;
use In2code\Femanager\Utility\BackendUserUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\UserUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Error\Http\UnauthorizedException;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
                'filter' => $filter,
            ]
        );
        $this->addDefaultViewVariables();
        return $this->htmlResponse();
    }

    public function showAction(?User $user = null): ResponseInterface
    {
        $this->view->assign('user', $this->getUser($user));
        $this->addDefaultViewVariables();
        return $this->htmlResponse();
    }

    /**
     * @throws \Exception
     */
    public function imageDeleteAction(User $user): ResponseInterface
    {
        $currentUser = UserUtility::getCurrentUser();
        if ($currentUser && $currentUser->getUid() === $user->getUid()) {
            $user->setImage(GeneralUtility::makeInstance(ObjectStorage::class));
            $this->userRepository->update($user);
            $this->logUtility->log(Log::STATUS_PROFILEUPDATEIMAGEDELETE, $user);
            $this->addFlashMessage(LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEIMAGEDELETE));
        } else {
            $this->logUtility->log(Log::STATUS_PROFILEUPDATENOTAUTHORIZED, $user);
            $this->addFlashMessage(
                LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATENOTAUTHORIZED),
                '',
                ContextualFeedbackSeverity::ERROR
            );
        }

        return $this->redirectToUri(
            $this->contentObject->typoLink_URL(
                [
                    'parameter' => $this->request->getAttribute('frontend.page.information')->getId(),
                ]
            )
        );
    }

    /**
     * @throws NoSuchArgumentException
     */
    public function validateAction(): ResponseInterface
    {
        $extbaseArguments = $this->request->getAttribute('extbase');
        $validation = $extbaseArguments->getArgument('validation') ?? '';
        $value = $extbaseArguments->getArgument('value') ?? '';
        $field = $extbaseArguments->getArgument('field') ?? '';
        $user = $extbaseArguments->getArgument('user') ?? null;
        $additionalValue = $extbaseArguments->getArgument('additionalValue') ?? '';
        $pluginUid = (int)$extbaseArguments->getArgument('plugin');
        $pluginNamespace = $extbaseArguments->getArgument('pluginName') ?? '';
        $referrerAction = $extbaseArguments->getArgument('referrerAction') ?? '';

        if ($user !== null) {
            $user = $this->userRepository->findByUid((int)$user);
        }

        $clientsideValidator = GeneralUtility::makeInstance(ClientsideValidator::class);
        $result = $clientsideValidator
            ->setValidationSettingsString($validation)
            ->setPluginNamespace($pluginNamespace)
            ->setValue($value)
            ->setFieldName($field)
            ->setUser($user)
            ->setAdditionalValue($additionalValue)
            ->setPluginUid($pluginUid)
            ->setReferrerActionName($referrerAction)
            ->validateField();

        $this->view->assignMultiple(
            [
                'isValid' => $result,
                'messages' => $clientsideValidator->getMessages(),
                'validation' => $validation,
                'value' => $value,
                'fieldname' => $field,
                'user' => $user,
            ]
        );
        return $this->jsonResponse();
    }

    /**
     * Simulate frontenduser login for backend adminstrators only
     *
     * @throws UnauthorizedException
     */
    public function loginAsAction(User $user, int $redirectPid = 1): ResponseInterface
    {
        $this->eventDispatcher->dispatch(new ImpersonateEvent($user, $GLOBALS['BE_USER']?->user['uid']));

        if (!BackendUserUtility::isAdmin()) {
            $this->logUtility->log(
                LOG::STATUS_LOGIN_AS_DENIED,
                $user,
                [
                    'backendUser' => [
                        'uid' => $GLOBALS['BE_USER']?->user['uid'],
                        'username' => $GLOBALS['BE_USER']->user['username']
                    ]
                ]
            );
            $this->persistenceManager->persistAll();
            throw new UnauthorizedException(LocalizationUtility::translate('error_not_authorized'), 1516373787864);
        }

        $redirectUri = $this->uriBuilder
            ->setTargetPageUid($redirectPid)
            ->setCreateAbsoluteUri(true)
            ->build();

        $this->logUtility->log(
            LOG::STATUS_LOGIN_AS,
            $user,
            [
                'backendUser' => [
                    'uid' => $GLOBALS['BE_USER']?->user['uid'],
                    'username' => $GLOBALS['BE_USER']->user['username']
                ]
            ]
        );

        // create a new session for the frontend user
        UserUtility::login($user);

        return new RedirectResponse($redirectUri);
    }

    /**
     * @return User
     */
    protected function getUser(?User $user = null)
    {
        if (!$user instanceof \In2code\Femanager\Domain\Model\User) {
            if (is_numeric($this->settings['show']['user'])) {
                $user = $this->userRepository->findByUid($this->settings['show']['user']);
            } elseif ($this->settings['show']['user'] === '[this]') {
                $user = $this->user;
            }
        }

        return $user;
    }
}
