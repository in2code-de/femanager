<?php

declare(strict_types=1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Validator\ClientsideValidator;
use In2code\Femanager\Event\ImpersonateEvent;
use In2code\Femanager\Utility\BackendUserUtility;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\HashUtility;
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
        $users = $this->userRepository->findByUsergroups(
            $this->settings['list']['usergroup'] ?? '',
            $this->settings,
            $filter
        );
        $showHashes = [];
        foreach ($users as $user) {
            $showHashes[$user->getUid()] = HashUtility::createHashForUser($user, 'show');
        }

        $this->view->assignMultiple(
            [
                'users' => $users,
                'showHashes' => $showHashes,
                'filter' => $filter,
            ]
        );
        $this->addDefaultViewVariables();
        return $this->htmlResponse();
    }

    public function showAction(?User $user = null, string $hash = ''): ResponseInterface
    {
        $user = $this->getUser($user, $hash);
        $this->view->assignMultiple([
            'user' => $user,
            'showHash' => $user instanceof User ? HashUtility::createHashForUser($user, 'show') : '',
        ]);
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

        // this action is only allowed for admins or backend users, which have the UserTS setting activated
        if (!BackendUserUtility::isAdmin() && !ConfigurationUtility::isEnableLoginAsActive()) {
            $this->logUtility->log(
                LOG::STATUS_LOGIN_AS_DENIED,
                $user,
                [
                    'backendUser' => [
                        'uid' => $GLOBALS['BE_USER']?->user['uid'],
                        'username' => $GLOBALS['BE_USER']->user['username'],
                    ],
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
                    'username' => $GLOBALS['BE_USER']->user['username'],
                ],
            ]
        );

        // create a new session for the frontend user
        UserUtility::login($user);

        return new RedirectResponse($redirectUri);
    }

    protected function getArgumentMissingFallbackActions(): array
    {
        return [
            'imageDelete' => 'list',
            'loginAs' => 'list',
        ];
    }

    protected function getUser(?User $user = null, string $hash = ''): ?User
    {
        $configuredUser = $this->settings['show']['user'] ?? '';
        if (is_numeric($configuredUser)) {
            return $this->userRepository->findByUid($configuredUser);
        }
        if ($configuredUser === '[this]') {
            return $this->user;
        }
        if ($user instanceof User && !HashUtility::validHash($hash, $user, 'show')) {
            throw new UnauthorizedException('Unauthorized user detail request', 1754916601);
        }

        return $user;
    }
}
