<?php

declare(strict_types = 1);

namespace In2code\Femanager\Domain\Validator;

use In2code\Femanager\Domain\Model\User;

interface ValidationConditionInterface
{
    public function shouldValidate(User $user, string $fieldName, array $validationSettings): bool;
}
