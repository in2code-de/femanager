<?php
declare(strict_types = 1);
namespace In2code\Femanager\Finisher;

use In2code\Femanager\Domain\Model\User;

/**
 * Interface FinisherInterface
 */
interface FinisherInterface
{

    /**
     * @return User
     */
    public function getUser();

    /**
     * @param User $user
     * @return AbstractFinisher
     */
    public function setUser($user);

    /**
     * Get settings
     *
     * @return array
     */
    public function getSettings();

    /**
     * Set settings
     *
     * @param array $settings
     * @return AbstractFinisher
     */
    public function setSettings($settings);

    public function getActionMethodName();

    /**
     * @param null $actionMethodName
     * @return AbstractFinisher
     */
    public function setActionMethodName($actionMethodName);

    public function initializeFinisher();
}
