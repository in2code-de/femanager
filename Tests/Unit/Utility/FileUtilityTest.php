<?php
namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Tests\Helper\TestingHelper;
use In2code\Femanager\Utility\FileUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;
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

    /**
     * @return void
     */
    public function setUp()
    {
        TestingHelper::setDefaultConstants();
    }

    /**
     * @return void
     * @covers ::createFolderIfNotExists
     */
    public function testCreateFolderIfNotExists()
    {
        $testpath = TestingHelper::getWebRoot() . 'fileadmin/';

        FileUtility::createFolderIfNotExists($testpath);
        $this->assertDirectoryExists($testpath);
        GeneralUtility::rmdir($testpath);
    }

    /**
     * @return void
     * @covers ::createFolderIfNotExists
     */
    public function testCreateFolderIfNotExistsNotCreated()
    {
        $testpath = TestingHelper::getWebRoot() . 'fileadmin/(ßü_$test/';
        $this->expectExceptionCode(1516373962125);
        FileUtility::createFolderIfNotExists($testpath);
    }

    /**
     * @return void
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
            $this->assertSame($expected, $path);
        }
    }
}
