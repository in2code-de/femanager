<?php

declare(strict_types=1);
namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Service\AutoAdminConfirmation\ConfirmationInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class AutoAdminConfirmationService
 */
class AutoAdminConfirmationService
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * @var string
     */
    protected $confirmInterface = ConfirmationInterface::class;

    /**
     * AutoAdminConfirmationService constructor.
     *
     * @param User $user
     * @param array $settings
     * @param ContentObjectRenderer $contentObject
     */
    public function __construct(User $user, array $settings, ContentObjectRenderer $contentObject)
    {
        $this->user = $user;
        $this->settings = $settings;
        $this->contentObject = $contentObject;
    }

    /**
     * Loop through all AutoAdminConfirmation classes
     *
     * @return bool
     */
    public function isAutoAdminConfirmationFullfilled(): bool
    {
        $autoConfirmation = false;
        foreach ($this->getConfirmationClasses() as $classConfiguration) {
            /** @var ConfirmationInterface $confirmation */
            $confirmation = GeneralUtility::makeInstance(
                $classConfiguration['class'],
                $classConfiguration['config'],
                $this->user,
                $this->settings,
                $this->contentObject
            );
            if ($confirmation->isAutoConfirmed() === true) {
                $autoConfirmation = true;
            }
        }
        return $autoConfirmation;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getConfirmationClasses(): array
    {
        $classes = [];
        if (!empty($this->settings['autoAdminConfirmation'])) {
            foreach ($this->settings['autoAdminConfirmation'] as $configuration) {
                $className = $configuration['class'];
                if (!class_exists($className)) {
                    throw new \UnexpectedValueException(
                        'Class ' . $className . ' does not exists - check if file was loaded with autoloader',
                        1516373867533
                    );
                }
                if (!is_subclass_of($className, $this->confirmInterface)) {
                    throw new \UnexpectedValueException(
                        'Class ' . $className . ' does not implement interface ' . $this->confirmInterface,
                        1516373878882
                    );
                }
                $classes[] = $configuration;
            }
        }
        return $classes;
    }
}
