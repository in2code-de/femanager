<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Validator\ServersideValidator;
use In2code\Femanager\Utility\LocalizationUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

class ValidationService
{
    public function __construct(
        protected ValidatorResolver $validatorResolver
    ) {
    }

    /**
     * @return array<FlashMessage>
     */
    public function doServersideValidation(User $user, ServerRequestInterface $request): array
    {
        $validationErrors = [];
        $validator = $this->validatorResolver->createValidator(ServersideValidator::class, [], $request);
        $results = $validator->validate($user);

        if ($results->hasErrors()) {
            foreach ($results->getFlattenedErrors() as $errors) {
                foreach ($errors as $error) {
                    $validationErrors[] = GeneralUtility::makeInstance(
                        FlashMessage::class,
                        LocalizationUtility::translate($error['message'], 'femanager', $error->getArguments()),
                        $error->getTitle(),
                        ContextualFeedbackSeverity::ERROR,
                    );
                }
            }
        }

        return $validationErrors;
    }
}
