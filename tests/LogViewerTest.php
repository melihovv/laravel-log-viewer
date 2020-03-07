<?php

namespace Melihovv\LaravelLogViewer\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Melihovv\LaravelLogViewer\Facades\LogViewer as LogViewerFacade;
use Melihovv\LaravelLogViewer\ServiceProvider;
use Orchestra\Testbench\TestCase;

class LogViewerTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'LogViewer' => LogViewerFacade::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('log-viewer.base_dir', $this->getBaseDir());
    }

    private function getBaseDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'logs';
    }

    protected function setUp(): void
    {
        parent::setUp();

        File::makeDirectory($this->getBaseDir());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory($this->getBaseDir());
    }

    /**
     * @dataProvider getLogsFromCurrentFileProvider
     *
     * @param callable $setupCb
     * @param callable $assertCb
     * @test
     */
    public function get_logs_from_current_file(
        callable $setupCb,
        callable $assertCb
    ) {
        File::put($this->getBaseDir().'/laravel.log', '');

        $setupCb($this);

        $logs = LogViewerFacade::getLogsFromCurrentFile();

        $assertCb($this, $logs);
    }

    public function getLogsFromCurrentFileProvider()
    {
        return [
            'current file is no set' => [
                function ($testcase) {
                },
                function ($testcase) {
                    $testcase->assertEquals([], LogViewerFacade::getLogsFromCurrentFile());
                },
            ],
            'file size is more than max file size' => [
                function ($testcase) {
                    LogViewerFacade::setCurrentFile('/laravel.log');
                    File::shouldReceive('size')
                        ->andReturn(
                            Config::get('log-viewer.max_file_size') + 1
                        );
                    File::getFacadeRoot()->makePartial();
                },
                function ($testcase, $logs) {
                    $testcase->assertEquals(null, $logs);
                },
            ],
            'success' => [
                function ($testcase) {
                    LogViewerFacade::setCurrentFile('/laravel.log');
                    \File::append($this->getBaseDir().'/laravel.log', <<<'HERE'
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
     * @test
     */
    public function get_current_directory_content(
        callable $setupCb,
        array $expected
    ) {
        $setupCb();

        $content = LogViewerFacade::getCurrentDirectoryContent();

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
                    \File::put($this->getBaseDir().'/log1.txt', '');
                    \File::put($this->getBaseDir().'/log2.txt', '');
                    \File::makeDirectory($this->getBaseDir().'/subfolder');
                },
                [
                    (object) [
                        'path' => DIRECTORY_SEPARATOR.'log1.txt',
                        'name' => 'log1.txt',
                        'isFile' => true,
                        'isDir' => false,
                    ],
                    (object) [
                        'path' => DIRECTORY_SEPARATOR.'log2.txt',
                        'name' => 'log2.txt',
                        'isFile' => true,
                        'isDir' => false,
                    ],
                    (object) [
                        'path' => DIRECTORY_SEPARATOR.'subfolder',
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
     * @test
     */
    public function set_current_directory(
        callable $setupCb,
        $currentDir,
        callable $assertCb
    ) {
        $setupCb($this);

        LogViewerFacade::setCurrentDirectory($currentDir);

        $assertCb($this);
    }

    public function setCurrentDirectoryProvider()
    {
        return [
            'success' => [
                function ($testcase) {
                    \File::makeDirectory($this->getBaseDir().'/subfolder');
                },
                '/subfolder',
                function ($testcase) {
                    $testcase->assertEquals(
                        $this->getBaseDir().DIRECTORY_SEPARATOR.'subfolder',
                        LogViewerFacade::getCurrentDirectory()
                    );
                },
            ],
            'directory is not inside base directory' => [
                function ($testcase) {
                    $baseDir = $this->getBaseDir().DIRECTORY_SEPARATOR.'newBaseDir';
                    \File::makeDirectory($baseDir);
                    LogViewerFacade::setBaseDirectory($baseDir);

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
     * @test
     */
    public function set_current_file($setupCb, $currentFile, $assertCb)
    {
        $setupCb($this);

        LogViewerFacade::setCurrentFile($currentFile);

        $assertCb($this);
    }

    public function setCurrentFileProvider()
    {
        return [
            'success' => [
                function ($testcase) {
                    File::put($this->getBaseDir().'/log.txt', '');
                },
                '/log.txt',
                function ($testcase) {
                    $testcase->assertEquals(
                        $this->getBaseDir().DIRECTORY_SEPARATOR.'log.txt',
                        LogViewerFacade::getCurrentFile()
                    );
                    $testcase->assertEquals(
                        $this->getBaseDir(),
                        LogViewerFacade::getCurrentDirectory()
                    );
                },
            ],
            'current directory must be directory of current file' => [
                function ($testcase) {
                    File::makeDirectory($this->getBaseDir().'/subfolder');
                    File::put($this->getBaseDir().'/subfolder/log.txt', '');
                },
                '/subfolder/log.txt',
                function ($testcase) {
                    $testcase->assertEquals(
                        $this->getBaseDir().DIRECTORY_SEPARATOR.'subfolder'
                        .DIRECTORY_SEPARATOR.'log.txt',
                        LogViewerFacade::getCurrentFile()
                    );
                    $testcase->assertEquals(
                        $this->getBaseDir().DIRECTORY_SEPARATOR.'subfolder',
                        LogViewerFacade::getCurrentDirectory()
                    );
                },
            ],
            'file is not inside base directory' => [
                function ($testcase) {
                    $baseDir = $this->getBaseDir().DIRECTORY_SEPARATOR.'newBaseDir';
                    File::makeDirectory($baseDir);
                    LogViewerFacade::setBaseDirectory($baseDir);

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

    /** @test */
    public function get_current_file_relative_to_base_dir()
    {
        File::put($this->getBaseDir().'/laravel.log', '');
        LogViewerFacade::setCurrentFile('/laravel.log');
        $this->assertEquals(
            DIRECTORY_SEPARATOR.'laravel.log',
            LogViewerFacade::getCurrentFileRelativeToBaseDir()
        );
    }

    /** @test */
    public function get_current_directory_relative_to_base_dir()
    {
        File::makeDirectory($this->getBaseDir().'/subdir');
        LogViewerFacade::setCurrentDirectory('/subdir');
        $this->assertEquals(
            DIRECTORY_SEPARATOR.'subdir',
            LogViewerFacade::getCurrentDirectoryRelativeToBaseDir()
        );
    }

    /**
     * @dataProvider getRelativePathToCurrentDirectoryParentProvider
     *
     * @param callable $assertCb
     * @test
     */
    public function get_relative_path_to_current_directory_parent(
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
                        LogViewerFacade::getRelativePathToCurrentDirectoryParent()
                    );
                },
            ],
            'cur dir does not equals base dir: subdir' => [
                function ($testcase) {
                    File::makeDirectory($this->getBaseDir().'/subdir');
                    LogViewerFacade::setCurrentDirectory('/subdir');

                    $testcase->assertEquals(
                        DIRECTORY_SEPARATOR,
                        LogViewerFacade::getRelativePathToCurrentDirectoryParent()
                    );
                },
            ],
            'cur dir does not equals base dir: subsubdir' => [
                function ($testcase) {
                    File::makeDirectory($this->getBaseDir().'/subdir/subsubdir', 0755, true);
                    LogViewerFacade::setCurrentDirectory('/subdir/subsubdir');

                    $testcase->assertEquals(
                        DIRECTORY_SEPARATOR.'subdir',
                        LogViewerFacade::getRelativePathToCurrentDirectoryParent()
                    );
                },
            ],
        ];
    }

    /**
     * @dataProvider isCurrentDirectoryBaseProvider
     *
     * @param callable $assertCb
     * @test
     */
    public function is_current_directory_base(callable $assertCb)
    {
        $assertCb($this);
    }

    public function isCurrentDirectoryBaseProvider()
    {
        return [
            'true' => [
                function ($testcase) {
                    $testcase->assertTrue(LogViewerFacade::isCurrentDirectoryBase());
                },
            ],
            'false' => [
                function ($testcase) {
                    File::makeDirectory($this->getBaseDir().'/subdir');
                    LogViewerFacade::setCurrentDirectory('/subdir');

                    $testcase->assertFalse(LogViewerFacade::isCurrentDirectoryBase());
                },
            ],
        ];
    }

    /**
     * @dataProvider getParentDirectoriesProvider
     *
     * @param callable $setupCb
     * @param callable $assertCb
     * @test
     */
    public function get_parent_directories(
        callable $setupCb,
        callable $assertCb
    ) {
        $setupCb($this);

        $dirs = LogViewerFacade::getParentDirectories();

        $assertCb($this, $dirs);
    }

    public function getParentDirectoriesProvider()
    {
        return [
            'current directory is base directory' => [
                function ($testcase) {
                },
                function ($testcase, $dirs) {
                    $testcase->assertEquals([], $dirs);
                },
            ],
            'current directory is one level down of base directory' => [
                function ($testcase) {
                    File::makeDirectory($this->getBaseDir().'/subdir');
                    LogViewerFacade::setCurrentDirectory('/subdir');
                },
                function ($testcase, $dirs) {
                    $testcase->assertEquals([
                        DIRECTORY_SEPARATOR,
                    ], $dirs);
                },
            ],
            'current directory is two level down of base directory' => [
                function ($testcase) {
                    File::makeDirectory($this->getBaseDir().'/subdir/subsubdir', 0755, true);
                    LogViewerFacade::setCurrentDirectory('/subdir/subsubdir');
                },
                function ($testcase, $dirs) {
                    $testcase->assertEquals([
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR.'subdir',
                    ], $dirs);
                },
            ],
        ];
    }
}
