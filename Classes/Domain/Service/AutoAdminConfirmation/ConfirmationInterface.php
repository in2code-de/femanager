<?php
declare(strict_types=1);
namespace In2code\Femanager\Domain\Service\AutoAdminConfirmation;

/**
 * Interface ConfirmationInterface
 */
interface ConfirmationInterface
{
    /**
     * @return bool
     */
    public function isAutoConfirmed(): bool;
}
