<?php
declare(strict_types=1);
namespace In2code\Femanager\Tests\Unit\Fixture\Domain\Validator;

use In2code\Femanager\Domain\Validator\AbstractValidator as AbstractValidatorFemanager;

/**
 * Class AbstractValidator
 */
class AbstractValidator extends AbstractValidatorFemanager
{
    public function isValid($value)
    {
        parent::isValid($value);
    }
}
