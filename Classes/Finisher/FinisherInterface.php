<?php
declare(strict_types=1);
namespace In2code\Femanager\Finisher;

use In2code\Femanager\Domain\Model\User;

/**
 * Interface FinisherInterface
 *
 * @package In2code\Femanager\Finisher
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

    /**
     * @return null
     */
    public function getActionMethodName();

    /**
     * @param null $actionMethodName
     * @return AbstractFinisher
     */
    public function setActionMethodName($actionMethodName);

    /**
     * @return void
     */
    public function initializeFinisher();
}
