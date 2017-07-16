<?php
declare(strict_types=1);
namespace In2code\Femanager\DataProcessor;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class AbstractDataProcessor
 */
abstract class AbstractDataProcessor implements DataProcessorInterface
{

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var null
     */
    protected $contentObject = null;

    /**
     * AbstractDataProcessor constructor.
     *
     * @param array $configuration
     * @param array $settings
     * @param ContentObjectRenderer $contentObject
     */
    public function __construct(array $configuration, array $settings, ContentObjectRenderer $contentObject)
    {
        $this->configuration = $configuration;
        $this->settings = $settings;
        $this->contentObject = $contentObject;
    }

    /**
     * @return void
     */
    public function initializeDataProcessor()
    {
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function getConfiguration(string $path = '')
    {
        $configuration = $this->configuration;
        if (!empty($path)) {
            $configuration = ArrayUtility::getValueByPath($configuration, $path, '.');
        }
        return $configuration;
    }
}
