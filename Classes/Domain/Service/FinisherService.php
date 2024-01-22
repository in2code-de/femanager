<?php

declare(strict_types=1);
namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Finisher\AbstractFinisher;
use In2code\Femanager\Utility\StringUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class FinisherService
 */
class FinisherService
{
    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * Classname
     *
     * @var string
     */
    protected $class = '';

    /**
     * Path that should be required
     *
     * @var string|null
     */
    protected $requirePath;

    /**
     * Finisher Configuration
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * @var User
     */
    protected $user;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Controller actionName - usually "createAction" or "confirmationAction"
     *
     * @var null
     */
    protected $actionMethodName;

    /**
     * @var string
     */
    protected $finisherInterface = 'In2code\Femanager\Finisher\FinisherInterface';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return FinisherService
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRequirePath()
    {
        return $this->requirePath;
    }

    /**
     * Set require path and do a require_once
     *
     * @param string|null $requirePath
     * @return FinisherService
     */
    public function setRequirePath($requirePath)
    {
        $this->requirePath = $requirePath;
        if ($this->getRequirePath() && file_exists($this->getRequirePath())) {
            require_once($this->getRequirePath());
        }
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
     * @return FinisherService
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return FinisherService
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
     * @return FinisherService
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
        return $this;
    }

    public function getActionMethodName()
    {
        return $this->actionMethodName;
    }

    /**
     * @param null $actionMethodName
     * @return FinisherService
     */
    public function setActionMethodName($actionMethodName)
    {
        $this->actionMethodName = $actionMethodName;
        return $this;
    }

    /**
     * Start implementation
     *
     * @throws \Exception
     */
    public function start()
    {
        if (!class_exists($this->getClass())) {
            throw new \UnexpectedValueException(
                'Class ' . $this->getClass() . ' does not exists - check if file was loaded with autoloader',
                1516373888508
            );
        }
        if (is_subclass_of($this->getClass(), $this->finisherInterface)) {
            /** @var AbstractFinisher $finisher */
            $finisher = $this->objectManager->get(
                $this->getClass(),
                $this->getUser(),
                $this->getConfiguration(),
                $this->getSettings(),
                $this->getActionMethodName(),
                $this->contentObject
            );
            $finisher->initializeFinisher();
            $this->callFinisherMethods($finisher);
        } else {
            throw new \UnexpectedValueException(
                'Finisher does not implement ' . $this->finisherInterface,
                1516373899775
            );
        }
    }

    /**
     * Call methods in finisher class
     *
     * @param AbstractFinisher $finisher
     */
    protected function callFinisherMethods(AbstractFinisher $finisher)
    {
        foreach (get_class_methods($finisher) as $method) {
            if (!StringUtility::endsWith($method, 'Finisher') || strpos($method, 'initialize') === 0) {
                continue;
            }
            $this->callInitializeFinisherMethod($finisher, $method);
            $finisher->{$method}();
        }
    }

    /**
     * Call initializeFinisherMethods like "initializeSaveFinisher()"
     *
     * @param AbstractFinisher $finisher
     * @param string $finisherMethod
     */
    protected function callInitializeFinisherMethod(AbstractFinisher $finisher, $finisherMethod)
    {
        if (method_exists($finisher, 'initialize' . ucfirst($finisherMethod))) {
            $finisher->{'initialize' . ucfirst($finisherMethod)}();
        }
    }

    /**
     * @param User $user
     * @param array $settings
     * @param ContentObjectRenderer $contentObject
     */
    public function __construct(User $user, array $settings, ContentObjectRenderer $contentObject)
    {
        $this->setUser($user);
        $this->setSettings($settings);
        $this->contentObject = $contentObject;
    }
}
