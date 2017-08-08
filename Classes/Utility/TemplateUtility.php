<?php
declare(strict_types=1);
namespace In2code\Femanager\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class TemplateUtility
 */
class TemplateUtility extends AbstractUtility
{

    /**
     * Get absolute path for templates with fallback
     *        In case of multiple paths this will just return the first one.
     *        See getTemplateFolders() for an array of paths.
     *
     * @param string $part "template", "partial", "layout"
     * @return string
     * @see getTemplateFolders()
     */
    public static function getTemplateFolder($part = 'template')
    {
        $matches = self::getTemplateFolders($part);
        return !empty($matches) ? $matches[0] : '';
    }

    /**
     * Get absolute paths for templates with fallback
     *        Returns paths from *RootPaths and *RootPath and "hardcoded"
     *        paths pointing to the EXT:femanager-resources.
     *
     * @param string $part "template", "partial", "layout"
     * @param boolean $returnAllPaths Default: FALSE, If FALSE only paths
     *        for the first configuration (Paths, Path, hardcoded)
     *        will be returned. If TRUE all (possible) paths will be returned.
     * @return array
     */
    public static function getTemplateFolders($part = 'template', $returnAllPaths = false)
    {
        $templatePaths = [];
        $configuration = self::getConfigurationManager()
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'femanager');
        if (!empty($configuration['view'][$part . 'RootPaths'])) {
            $templatePaths = $configuration['view'][$part . 'RootPaths'];
            $templatePaths = array_values($templatePaths);
        }
        if ($returnAllPaths || empty($templatePaths)) {
            $path = $configuration['view'][$part . 'RootPath'];
            if (!empty($path)) {
                $templatePaths[] = $path;
            }
        }
        if ($returnAllPaths || empty($templatePaths)) {
            $templatePaths[] = 'EXT:femanager/Resources/Private/' . ucfirst($part) . 's/';
        }
        $templatePaths = array_unique($templatePaths);
        $absolutePaths = [];
        foreach ($templatePaths as $templatePath) {
            $absolutePaths[] = GeneralUtility::getFileAbsFileName($templatePath);
        }
        return $absolutePaths;
    }

    /**
     * Return path and filename for a file or path.
     *        Only the first existing file/path will be returned.
     *        respect *RootPaths and *RootPath
     *
     * @param string $pathAndFilename e.g. Email/Name.html
     * @param string $part "template", "partial", "layout"
     * @return string Filename/path
     */
    public static function getTemplatePath($pathAndFilename, $part = 'template')
    {
        $matches = self::getTemplatePaths($pathAndFilename, $part);
        return !empty($matches) ? end($matches) : '';
    }

    /**
     * Return path and filename for one or many files/paths.
     *        Only existing files/paths will be returned.
     *        respect *RootPaths and *RootPath
     *
     * @param string $pathAndFilename Path/filename (Email/Name.html) or path
     * @param string $part "template", "partial", "layout"
     * @return array All existing matches found
     */
    public static function getTemplatePaths($pathAndFilename, $part = 'template')
    {
        $pathAndFilenames = [];
        $absolutePaths = self::getTemplateFolders($part, true);
        foreach ($absolutePaths as $absolutePath) {
            if (file_exists($absolutePath . $pathAndFilename)) {
                $pathAndFilenames[] = $absolutePath . $pathAndFilename;
            }
        }
        return $pathAndFilenames;
    }

    /**
     * Get standaloneview with default properties
     *
     * @param string $controllerName
     * @param string $extensionName
     * @param string $pluginName
     * @param string $format
     * @return StandaloneView
     */
    public static function getDefaultStandAloneView(
        $controllerName = 'New',
        $extensionName = 'Femanager',
        $pluginName = 'Pi1',
        $format = 'html'
    ) {
        /** @var StandaloneView $standAloneView */
        $standAloneView = self::getObjectManager()->get(StandaloneView::class);
        $standAloneView->getRequest()->setControllerExtensionName($extensionName);
        $standAloneView->getRequest()->setPluginName($pluginName);
        $standAloneView->getRequest()->setControllerName($controllerName);
        $standAloneView->setFormat($format);
        $standAloneView->setLayoutRootPaths(TemplateUtility::getTemplateFolders('layout'));
        $standAloneView->setPartialRootPaths(TemplateUtility::getTemplateFolders('partial'));
        return $standAloneView;
    }
}
