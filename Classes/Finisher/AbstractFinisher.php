<?php
declare(strict_types=1);
namespace In2code\Femanager\Finisher;

use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class AbstractFinisher
 */
abstract class AbstractFinisher implements FinisherInterface
{

    /**
     * @var User
     */
    protected $user;

    /**
     * Extension settings
     *
     * @var array
     */
    protected $settings;

    /**
     * Finisher service configuration
     *
     * @var array
     */
    protected $configuration;

    /**
     * Controller actionName - usually "createAction" or "confirmationAction"
     *
     * @var null
     */
    protected $actionMethodName = null;

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return AbstractFinisher
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     * @return AbstractFinisher
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param array $configuration
     * @return AbstractFinisher
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return null
     */
    public function getActionMethodName()
    {
        return $this->actionMethodName;
    }

    /**
     * @param null $actionMethodName
     * @return AbstractFinisher
     */
    public function setActionMethodName($actionMethodName)
    {
        $this->actionMethodName = $actionMethodName;
        return $this;
    }

    /**
     * @return void
     */
    public function initializeFinisher()
    {
    }

    /**
     * @param User $user
     * @param array $configuration
     * @param array $settings
     * @param ContentObjectRenderer $contentObject
     * @param string $actionMethodName
     */
    public function __construct(
        User $user,
        array $configuration,
        array $settings,
        $actionMethodName,
        ContentObjectRenderer $contentObject
    ) {
        $this->setUser($user);
        $this->setConfiguration($configuration);
        $this->setSettings($settings);
        $this->setActionMethodName($actionMethodName);
        $this->contentObject = $contentObject;
    }
}
