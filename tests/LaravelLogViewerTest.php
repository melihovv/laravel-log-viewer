<?php

namespace Melihovv\LaravelLogViewer\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Melihovv\LaravelLogViewer\Facades\LaravelLogViewer as LaravelLogViewerFacade;
use Melihovv\LaravelLogViewer\LaravelLogViewerServiceProvider;
use Orchestra\Testbench\TestCase;

class LaravelLogViewerTest extends TestCase
{
    const BASEDIR = __DIR__ . DIRECTORY_SEPARATOR . 'logs';

    protected function setUp()
    {
        parent::setUp();

        File::makeDirectory(self::BASEDIR);
    }

    protected function tearDown()
    {
        File::deleteDirectory(self::BASEDIR);

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [LaravelLogViewerServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'LogViewer' => LaravelLogViewerFacade::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('laravel-log-viewer.base_dir', self::BASEDIR);
    }

    /**
     * @dataProvider getLogsFromCurrentFileProvider
     *
     * @param callable $setupCb
     * @param callable $assertCb
     */
    public function testGetLogsFromCurrentFile(
        callable $setupCb,
        callable $assertCb
    ) {
        File::put(self::BASEDIR . '/laravel.log', '');

        $setupCb($this);

        $logs = \LogViewer::getLogsFromCurrentFile();

        $assertCb($this, $logs);
    }

    public function getLogsFromCurrentFileProvider()
    {
        return [
            'current file is no set' => [
                function ($testcase) {
                },
                function ($testcase) {
                    $testcase->assertEquals([], \LogViewer::getLogsFromCurrentFile());
                },
            ],
            'file size is more than max file size' => [
                function ($testcase) {
                    \LogViewer::setCurrentFile('/laravel.log');
                    File::shouldReceive('size')
                        ->andReturn(
                            Config::get('laravel-log-viewer.max_file_size') + 1
                        );
                    File::getFacadeRoot()->makePartial();
                },
                function ($testcase, $logs) {
                    $testcase->assertEquals(null, $logs);
                },
            ],
            'success' => [
                function ($testcase) {
                    \LogViewer::setCurrentFile('/laravel.log');
                    \File::append(self::BASEDIR . '/laravel.log', <<<'HERE'
[2017-01-04 05:21:25] local.INFO: hi
not match
[2017-01-04 05:22:25] production.ERROR: ho in Main.php:48
HERE
                    );
                },
                function ($testcase, $logs) {
                    $testcase->assertEquals([
                        (object) [
                            'context' => 'production',
                            'level' => 'error',
                            'levelClass' => 'danger',
                            'levelImg' => 'warning',
                            'date' => '2017-01-04 05:22:25',
                            'text' => 'ho',
                            'inFile' => 'Main.php:48',
                        ],
                        (object) [
                            'context' => 'local',
                            'level' => 'info',
                            'levelClass' => 'info',
                            'levelImg' => 'info',
                            'date' => '2017-01-04 05:21:25',
                            'text' => 'hi',
                            'inFile' => null,
                        ],
                    ], $logs);
                },
            ],
        ];
    }

    /**
     * @param callable $setupCb
     * @param array    $expected
     * @dataProvider getCurrentDirectoryContentProvider
     */
    public function testGetCurrentDirectoryContent(
        callable $setupCb,
        array $expected
    ) {
        $setupCb();

        $content = \LogViewer::getCurrentDirectoryContent();

        $this->assertEquals($expected, $content);
    }

    public function getCurrentDirectoryContentProvider()
    {
        return [
            'empty dir' => [
                function () {
                },
                [],
            ],
            'dir with files and subdir' => [
                function () {
                    \File::put(self::BASEDIR . '/log1.txt', '');
                    \File::put(self::BASEDIR . '/log2.txt', '');
                    \File::makeDirectory(self::BASEDIR . '/subfolder');
                },
                [
                    (object) [
                        'path' => DIRECTORY_SEPARATOR . 'log1.txt',
                        'name' => 'log1.txt',
                        'isFile' => true,
                        'isDir' => false,
                    ],
                    (object) [
                        'path' => DIRECTORY_SEPARATOR . 'log2.txt',
                        'name' => 'log2.txt',
                        'isFile' => true,
                        'isDir' => false,
                    ],
                    (object) [
                        'path' => DIRECTORY_SEPARATOR . 'subfolder',
                        'name' => 'subfolder',
                        'isFile' => false,
                        'isDir' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param callable $setupCb
     * @param string   $currentDir
     * @param callable $assertCb
     * @dataProvider setCurrentDirectoryProvider
     */
    public function testSetCurrentDirectory(
        callable $setupCb,
        $currentDir,
        callable $assertCb
    ) {
        $setupCb($this);

        \LogViewer::setCurrentDirectory($currentDir);

        $assertCb($this);
    }

    public function setCurrentDirectoryProvider()
    {
        return [
            'success' => [
                function ($testcase) {
                    \File::makeDirectory(self::BASEDIR . '/subfolder');
                },
                '/subfolder',
                function ($testcase) {
                    $testcase->assertEquals(
                        self::BASEDIR . DIRECTORY_SEPARATOR . 'subfolder',
                        \LogViewer::getCurrentDirectory()
                    );
                },
            ],
            'directory is not inside base directory' => [
                function ($testcase) {
                    $baseDir = self::BASEDIR . DIRECTORY_SEPARATOR . 'newBaseDir';
                    \File::makeDirectory($baseDir);
                    \LogViewer::setBaseDirectory($baseDir);

                    \File::makeDirectory("$baseDir/../notSubFolder");

                    $testcase->expectException(InvalidArgumentException::class);
                },
                '/../notSubFolder',
                function ($testcase) {
                },
            ],
            'directory does not exist' => [
                function ($testcase) {
                    $testcase->expectException(InvalidArgumentException::class);
                },
                '/../notSubFolder',
                function ($testcase) {
                },
            ],
        ];
    }

    /**
     * @param callable $setupCb
     * @param string   $currentFile
     * @param callable $assertCb
     * @dataProvider setCurrentFileProvider
     */
    public function testSetCurrentFile($setupCb, $currentFile, $assertCb)
    {
        $setupCb($this);

        \LogViewer::setCurrentFile($currentFile);

        $assertCb($this);
    }

    public function setCurrentFileProvider()
    {
        return [
            'success' => [
                function ($testcase) {
                    File::put(self::BASEDIR . '/log.txt', '');
                },
                '/log.txt',
                function ($testcase) {
                    $testcase->assertEquals(
                        self::BASEDIR . DIRECTORY_SEPARATOR . 'log.txt',
                        \LogViewer::getCurrentFile()
                    );
                    $testcase->assertEquals(
                        self::BASEDIR,
                        \LogViewer::getCurrentDirectory()
                    );
                },
            ],
            'current directory must be directory of current file' => [
                function ($testcase) {
                    File::makeDirectory(self::BASEDIR . '/subfolder');
                    File::put(self::BASEDIR . '/subfolder/log.txt', '');
                },
                '/subfolder/log.txt',
                function ($testcase) {
                    $testcase->assertEquals(
                        self::BASEDIR . DIRECTORY_SEPARATOR . 'subfolder'
                        . DIRECTORY_SEPARATOR . 'log.txt',
                        \LogViewer::getCurrentFile()
                    );
                    $testcase->assertEquals(
                        self::BASEDIR . DIRECTORY_SEPARATOR . 'subfolder',
                        \LogViewer::getCurrentDirectory()
                    );
                },
            ],
            'file is not inside base directory' => [
                function ($testcase) {
                    $baseDir = self::BASEDIR . DIRECTORY_SEPARATOR . 'newBaseDir';
                    File::makeDirectory($baseDir);
                    \LogViewer::setBaseDirectory($baseDir);

                    File::put("$baseDir/../notInSubfolder.log", '');

                    $testcase->expectException(InvalidArgumentException::class);
                },
                '/../notInSubfolder.log',
                function ($testcase) {
                },
            ],
            'file does not exist' => [
                function ($testcase) {
                    $testcase->expectException(InvalidArgumentException::class);
                },
                '/../notInSubfolder.log',
                function ($testcase) {
                },
            ],
        ];
    }

    public function testGetCurrentFileRelativeToBaseDir()
    {
        File::put(self::BASEDIR . '/laravel.log', '');
        \LogViewer::setCurrentFile('/laravel.log');
        $this->assertEquals(
            DIRECTORY_SEPARATOR . 'laravel.log',
            \LogViewer::getCurrentFileRelativeToBaseDir()
        );
    }

    public function testGetCurrentDirectoryRelativeToBaseDir()
    {
        File::makeDirectory(self::BASEDIR . '/subdir');
        \LogViewer::setCurrentDirectory('/subdir');
        $this->assertEquals(
            DIRECTORY_SEPARATOR . 'subdir',
            \LogViewer::getCurrentDirectoryRelativeToBaseDir()
        );
    }

    /**
     * @dataProvider getRelativePathToCurrentDirectoryParentProvider
     *
     * @param callable $assertCb
     */
    public function testGetRelativePathToCurrentDirectoryParent(
        callable $assertCb
    ) {
        $assertCb($this);
    }

    public function getRelativePathToCurrentDirectoryParentProvider()
    {
        return [
            'cur dir equals base dir' => [
                function ($testcase) {
                    $testcase->assertEquals(
                        DIRECTORY_SEPARATOR,
                        \LogViewer::getRelativePathToCurrentDirectoryParent()
                    );
                },
            ],
            'cur dir does not equals base dir: subdir' => [
                function ($testcase) {
                    File::makeDirectory(self::BASEDIR . '/subdir');
                    \LogViewer::setCurrentDirectory('/subdir');

                    $testcase->assertEquals(
                        DIRECTORY_SEPARATOR,
                        \LogViewer::getRelativePathToCurrentDirectoryParent()
                    );
                },
            ],
            'cur dir does not equals base dir: subsubdir' => [
                function ($testcase) {
                    File::makeDirectory(self::BASEDIR . '/subdir/subsubdir', 0755, true);
                    \LogViewer::setCurrentDirectory('/subdir/subsubdir');

                    $testcase->assertEquals(
                        DIRECTORY_SEPARATOR . 'subdir',
                        \LogViewer::getRelativePathToCurrentDirectoryParent()
                    );
                },
            ],
        ];
    }

    /**
     * @dataProvider isCurrentDirectoryBaseProvider
     *
     * @param callable $assertCb
     */
    public function testIsCurrentDirectoryBase(callable $assertCb)
    {
        $assertCb($this);
    }

    public function isCurrentDirectoryBaseProvider()
    {
        return [
            'true' => [
                function ($testcase) {
                    $testcase->assertTrue(\LogViewer::isCurrentDirectoryBase());
                },
            ],
            'false' => [
                function ($testcase) {
                    File::makeDirectory(self::BASEDIR . '/subdir');
                    \LogViewer::setCurrentDirectory('/subdir');

                    $testcase->assertFalse(\LogViewer::isCurrentDirectoryBase());
                },
            ],
        ];
    }
}
