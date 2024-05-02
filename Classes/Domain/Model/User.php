<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Model;

use DateTime;
use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class User
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class User extends AbstractEntity
{
    final public const TABLE_NAME = 'fe_users';

    /**
     * @var ObjectStorage<UserGroup>
     */
    protected $usergroup;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $firstName = '';

    /**
     * @var string
     */
    protected $middleName = '';

    /**
     * @var string
     */
    protected $lastName = '';

    /**
     * @var string
     */
    protected $address = '';

    /**
     * @var string
     */
    protected $telephone = '';

    /**
     * @var string
     */
    protected $fax = '';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $zip = '';

    /**
     * @var string
     */
    protected $city = '';

    /**
     * @var string
     */
    protected $country = '';

    /**
     * @var string
     */
    protected $www = '';

    /**
     * @var string
     */
    protected $company = '';

    /**
     * @var ObjectStorage<FileReference>
     */
    protected $image;

    /**
     * @var DateTime|null
     */
    protected $lastlogin;

    /**
     * @var string
     */
    protected $txFemanagerChangerequest;

    /**
     * @var DateTime
     */
    protected $crdate;

    /**
     * @var DateTime
     */
    protected $tstamp;

    /**
     * @var bool
     */
    protected $disable;

    /**
     * @var bool
     */
    protected $txFemanagerConfirmedbyuser;

    /**
     * @var bool
     */
    protected $txFemanagerConfirmedbyadmin;

    /**
     * @var bool
     */
    protected $isOnline = false;

    /**
     * @var bool
     */
    protected $ignoreDirty = false;

    /**
     * @var int
     */
    protected $gender = 99;

    /**
     * @var DateTime
     */
    protected $dateOfBirth;

    /**
     * termsAndConditions
     *
     * @var bool
     */
    protected $terms = false;

    /**
     * the datetime the user accepted the terms
     *
     * @var DateTime
     */
    protected $termsDateOfAcceptance;

    /**
     * @var string
     */
    protected $txExtbaseType;

    /**
     * Created Password in Cleartext (if generated Password)
     * will of course not be persistent and lives until runtime end
     *
     * @var string
     */
    protected $passwordAutoGenerated;

    /**
     * Constructs a new Front-End User
     */
    public function __construct(protected string $username = '', protected string $password = '')
    {
        $this->usergroup = new ObjectStorage();
        $this->image = new ObjectStorage();
    }

    /**
     * Sets the username value
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * Returns the username value
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Sets the password value
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * Returns the password value
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Sets the usergroups. Keep in mind that the property is called "usergroup"
     * although it can hold several usergroups.
     *
     * @param ObjectStorage<UserGroup> $usergroup
     */
    public function setUsergroup(ObjectStorage $usergroup)
    {
        $this->usergroup = $usergroup;
    }

    /**
     * Adds a usergroup to the frontend user
     */
    public function addUsergroup(UserGroup $usergroup)
    {
        $this->usergroup->attach($usergroup);
    }

    /**
     * Removes a usergroup from the frontend user
     */
    public function removeUsergroup(UserGroup $usergroup)
    {
        $this->usergroup->detach($usergroup);
    }

    /**
     * Returns the usergroups. Keep in mind that the property is called "usergroup"
     * although it can hold several usergroups.
     *
     * @return ObjectStorage<UserGroup> An object storage containing the usergroups
     */
    public function getUsergroup(): ObjectStorage
    {
        return $this->usergroup;
    }

    /**
     * Sets the name value
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Returns the name value
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the firstName value
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Returns the firstName value
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Sets the middleName value
     */
    public function setMiddleName(string $middleName)
    {
        $this->middleName = $middleName;
    }

    /**
     * Returns the middleName value
     */
    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    /**
     * Sets the lastName value
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * Returns the lastName value
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Sets the address value
     */
    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    /**
     * Returns the address value
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Sets the telephone value
     */
    public function setTelephone(string $telephone)
    {
        $this->telephone = $telephone;
    }

    /**
     * Returns the telephone value
     */
    public function getTelephone(): string
    {
        return $this->telephone;
    }

    /**
     * Sets the fax value
     */
    public function setFax(string $fax)
    {
        $this->fax = $fax;
    }

    /**
     * Returns the fax value
     */
    public function getFax(): string
    {
        return $this->fax;
    }

    /**
     * Sets the email value
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * Returns the email value
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Sets the title value
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Returns the title value
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the zip value
     */
    public function setZip(string $zip)
    {
        $this->zip = $zip;
    }

    /**
     * Returns the zip value
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * Sets the city value
     */
    public function setCity(string $city)
    {
        $this->city = $city;
    }

    /**
     * Returns the city value
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Sets the country value
     */
    public function setCountry(string $country)
    {
        $this->country = $country;
    }

    /**
     * Returns the country value
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Sets the www value
     */
    public function setWww(string $www)
    {
        $this->www = $www;
    }

    /**
     * Returns the www value
     */
    public function getWww(): string
    {
        return $this->www;
    }

    /**
     * Sets the company value
     */
    public function setCompany(string $company)
    {
        $this->company = $company;
    }

    /**
     * Returns the company value
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * Sets the image value
     *
     * @param ObjectStorage<FileReference> $image
     */
    public function setImage(ObjectStorage $image)
    {
        $this->image = $image;
    }

    /**
     * Gets the image value
     */
    public function getImage(): ObjectStorage|null
    {
        return $this->image;
    }

    /**
     * Sets the lastlogin value
     */
    public function setLastlogin(DateTime $lastlogin)
    {
        $this->lastlogin = $lastlogin;
    }

    /**
     * Returns the lastlogin value
     *
     * @return DateTime
     */
    public function getLastlogin()
    {
        return $this->lastlogin;
    }

    /**
     * @var string
     */
    protected $state = '';

    public function removeAllUsergroups()
    {
        $this->usergroup = new ObjectStorage();
    }

    /**
     * @param string $txFemanagerChangerequest
     * @return User
     */
    public function setTxFemanagerChangerequest($txFemanagerChangerequest)
    {
        $this->txFemanagerChangerequest = $txFemanagerChangerequest;
        return $this;
    }

    /**
     * @return string
     */
    public function getTxFemanagerChangerequest()
    {
        return $this->txFemanagerChangerequest;
    }

    /**
     * @param DateTime $crdate
     * @return User
     */
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCrdate()
    {
        if ($this->crdate === null) {
            // timestamp is zero
            $this->crdate = new DateTime('01.01.1970');
        }
        return $this->crdate;
    }

    /**
     * @param DateTime $tstamp
     * @return User
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * @param bool $disable
     * @return User
     */
    public function setDisable($disable)
    {
        $this->disable = $disable;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisable()
    {
        return $this->disable;
    }

    /**
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function setTxFemanagerConfirmedbyadmin(bool $txFemanagerConfirmedbyadmin): User
    {
        $this->txFemanagerConfirmedbyadmin = $txFemanagerConfirmedbyadmin;
        return $this;
    }

    public function getTxFemanagerConfirmedbyadmin(): bool
    {
        return $this->txFemanagerConfirmedbyadmin;
    }

    /**
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function setTxFemanagerConfirmedbyuser(bool $txFemanagerConfirmedbyuser): User
    {
        $this->txFemanagerConfirmedbyuser = $txFemanagerConfirmedbyuser;
        return $this;
    }

    public function getTxFemanagerConfirmedbyuser(): bool
    {
        return $this->txFemanagerConfirmedbyuser;
    }

    /**
     * @param bool $ignoreDirty
     * @return User
     */
    public function setIgnoreDirty($ignoreDirty)
    {
        $this->ignoreDirty = $ignoreDirty;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIgnoreDirty()
    {
        return $this->ignoreDirty;
    }

    /**
     * Returns the gender
     *
     * @return int $gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Sets the gender
     *
     * @param int $gender
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * Returns the dateOfBirth
     *
     * @return DateTime $dateOfBirth
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * Sets the dateOfBirth
     *
     * @param DateTime $dateOfBirth
     * @return User
     */
    public function setDateOfBirth($dateOfBirth)
    {
        if ($dateOfBirth instanceof DateTime) {
            $dateOfBirth->setTime(0, 0, 0);
        }
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    /**
     * Returns, whether the user has accepted terms and conditions
     */
    public function isTerms(): bool
    {
        return $this->terms;
    }

    /**
     * Set whether the user has accepted terms and conditions
     *
     * @return User
     */
    public function setTerms(bool $terms)
    {
        $this->terms = $terms;
        $this->setTermsDateOfAcceptance();
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getTermsDateOfAcceptance()
    {
        return $this->termsDateOfAcceptance;
    }

    /**
     * set terms date to now if it's not set yet
     *
     * @return User
     */
    protected function setTermsDateOfAcceptance()
    {
        if ($this->termsDateOfAcceptance === null) {
            $now = new DateTime();
            $this->termsDateOfAcceptance = $now;
        }
        return $this;
    }

    public function getIsOnline(): bool
    {
        return $this->isOnline();
    }

    /**
     * Check if last FE login was within the last 2h
     */
    public function isOnline(): bool
    {
        if (
            $this->getLastlogin() !== null
            && method_exists($this->getLastlogin(), 'getTimestamp')
            && $this->getLastlogin()->getTimestamp() > (time() - 2 * 60 * 60)
            && UserUtility::checkFrontendSessionToUser($this)
        ) {
            return true;
        }
        return $this->isOnline;
    }

    public function setTxExtbaseType(string $txExtbaseType): User
    {
        $this->txExtbaseType = $txExtbaseType;
        return $this;
    }

    public function getTxExtbaseType(): string
    {
        return $this->txExtbaseType;
    }

    public function getFirstImage()
    {
        $images = $this->getImage();
        foreach ($images as $image) {
            return $image;
        }
        return null;
    }

    public function setPasswordAutoGenerated(string $passwordAutoGenerated): User
    {
        $this->passwordAutoGenerated = $passwordAutoGenerated;
        return $this;
    }

    public function getPasswordAutoGenerated(): string|null
    {
        return $this->passwordAutoGenerated;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * Workaround to disable persistence in updateAction
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    public function _isDirty($propertyName = null): bool
    {
        return $this->isIgnoreDirty() ? false : parent::_isDirty($propertyName);
    }
}
