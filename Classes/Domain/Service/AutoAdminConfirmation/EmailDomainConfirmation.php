<?php
declare(strict_types=1);
namespace In2code\Femanager\Domain\Service\AutoAdminConfirmation;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class EmailDomainConfirmation
 */
class EmailDomainConfirmation extends AbstractConfirmation
{

    /**
     * @return bool
     */
    public function isAutoConfirmed(): bool
    {
        if (!$this->isException() && $this->isGivenDomainsPartOfEmail()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function isGivenDomainsPartOfEmail(): bool
    {
        $domains = GeneralUtility::trimExplode(',', $this->getConfig()['confirmByEmailDomains'], true);
        foreach ($domains as $domain) {
            if (stristr($this->getEmailDomain(), $domain)) {
                return true;
            }
        }
        return false;
    }

    protected function isException(): bool
    {
        $exceptionDomains =
            GeneralUtility::trimExplode(',', $this->getConfig()['confirmByEmailDomainsExceptions'], true);
        return in_array($this->getEmailDomain(), $exceptionDomains);
    }

    /**
     * Get domain of an email address: "alex@in2code.de" => "in2code.de"
     *
     * @return string
     */
    protected function getEmailDomain(): string
    {
        $email = $this->getUser()->getEmail();
        $parts = explode('@', $email);
        return $parts[1];
    }
}
