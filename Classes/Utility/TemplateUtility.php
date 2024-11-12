<?php

declare(strict_types=1);

namespace In2code\Femanager\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class TemplateUtility
 * @codeCoverageIgnore
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
    public static function getTemplateFolder(string $part = 'template')
    {
        $matches = self::getTemplateFolders($part);
        return $matches === [] ? '' : $matches[0];
    }

    /**
     * Get absolute paths for templates with fallback
     *        Returns paths from *RootPaths and *RootPath and "hardcoded"
     *        paths pointing to the EXT:femanager-resources.
     *
     * @param string $part "template", "partial", "layout"
     * @param bool $returnAllPaths Default: FALSE, If FALSE only paths
     *        for the first configuration (Paths, Path, hardcoded)
     *        will be returned. If TRUE all (possible) paths will be returned.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function getTemplateFolders(string $part = 'template', $returnAllPaths = false): array
    {
        $templatePaths = [];
        $configuration = self::getConfigurationManager()
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'femanager');
        if (!empty($configuration['view'][$part . 'RootPaths'] ?? '')) {
            $templatePaths = $configuration['view'][$part . 'RootPaths'] ?? '';
            $templatePaths = array_values($templatePaths);
        }

        if ($returnAllPaths || $templatePaths === []) {
            $path = $configuration['view'][$part . 'RootPath'] ?? '';
            if (!empty($path)) {
                $templatePaths[] = $path;
            }

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
    public static function getTemplatePath(string $pathAndFilename, string $part = 'template')
    {
        $matches = self::getTemplatePaths($pathAndFilename, $part);
        return $matches === [] ? '' : end($matches);
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
    public static function getTemplatePaths(string $pathAndFilename, string $part = 'template'): array
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
     */
    public static function getDefaultStandAloneView(
        RequestInterface|null $request = null,
        string $format = 'html'
    ): StandaloneView {
        /** @var StandaloneView $standAloneView */
        $standAloneView = GeneralUtility::makeInstance(StandaloneView::class);
        if ($request instanceof RequestInterface) {
            $standAloneView->setRequest($request);
        }

        $standAloneView->setFormat($format);
        $standAloneView->setLayoutRootPaths(TemplateUtility::getTemplateFolders('layout'));
        $standAloneView->setPartialRootPaths(TemplateUtility::getTemplateFolders('partial'));
        return $standAloneView;
    }
}
