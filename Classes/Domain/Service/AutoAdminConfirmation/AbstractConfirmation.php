<?php
declare(strict_types=1);
namespace In2code\Femanager\Domain\Service\AutoAdminConfirmation;

use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class AbstractConfirmation
 */
abstract class AbstractConfirmation implements ConfirmationInterface
{
    /**
     * @var array
     */
    protected $config = [];

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
     * AbstractConfirmation constructor.
     *
     * @param array $config
     * @param User $user
     * @param array $settings
     * @param ContentObjectRenderer $contentObject
     */
    public function __construct(array $config, User $user, array $settings, ContentObjectRenderer $contentObject)
    {
        $this->config = $config;
        $this->user = $user;
        $this->settings = $settings;
        $this->contentObject = $contentObject;
    }

    /**
     * Skip manual confirmation from admin?
     *
     * @return bool
     */
    public function isAutoConfirmed(): bool
    {
        return false;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @return ContentObjectRenderer
     */
    public function getContentObject(): ContentObjectRenderer
    {
        return $this->contentObject;
    }
}
