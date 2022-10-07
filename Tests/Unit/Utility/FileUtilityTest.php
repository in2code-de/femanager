<?php

namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Tests\Helper\TestingHelper;
use In2code\Femanager\Utility\FileUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FileUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\FileUtility
 */
class FileUtilityTest extends UnitTestCase
{
    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    public function setUp(): void
    {
        TestingHelper::setDefaultConstants();
    }

    /**
     * @covers ::createFolderIfNotExists
     */
    public function testCreateFolderIfNotExists()
    {
        $testpath = TestingHelper::getWebRoot() . 'fileadmin/';

        FileUtility::createFolderIfNotExists($testpath);
        self::assertDirectoryExists($testpath);
        GeneralUtility::rmdir($testpath);
    }

    /**
     * @covers ::getRelativeFolderFromAbsolutePath
     */
    public function testGetRelativeFolderFromAbsolutePath()
    {
        $paths = [
            'abc/' => 'abc/',
            'fileadmin/filename.pdf' => 'fileadmin/filename.pdf',
            GeneralUtility::getFileAbsFileName('fileadmin/') => 'fileadmin/',
            GeneralUtility::getFileAbsFileName('.Build/Web/') => '.Build/Web/',
        ];
        foreach ($paths as $given => $expected) {
            $path = str_replace('../', '', FileUtility::getRelativeFolderFromAbsolutePath($given));
            self::assertSame($expected, $path);
        }
    }
}
