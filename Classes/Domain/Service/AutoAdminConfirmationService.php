<?php
declare(strict_types=1);
namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Service\AutoAdminConfirmation\ConfirmationInterface;
use In2code\Femanager\Utility\ObjectUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class AutoAdminConfirmationService
 */
class AutoAdminConfirmationService
{
    /**
     * @var User
     */
    protected $user = null;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject = null;

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
            $confirmation = ObjectUtility::getObjectManager()->get(
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
                    throw new \Exception(
                        'Class ' . $className . ' does not exists - check if file was loaded with autoloader'
                    );
                }
                if (!is_subclass_of($className, $this->confirmInterface)) {
                    throw new \Exception(
                        'Class ' . $className . ' does not implement interface ' . $this->confirmInterface
                    );
                }
                $classes[] = $configuration;
            }
        }
        return $classes;
    }
}
