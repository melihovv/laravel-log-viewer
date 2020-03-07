<?php

namespace Melihovv\LaravelLogViewer\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Melihovv\LaravelLogViewer\Controller;
use Melihovv\LaravelLogViewer\Facades\LogViewer as LogViewerFacade;
use Melihovv\LaravelLogViewer\ServiceProvider;
use Orchestra\Testbench\BrowserKit\TestCase;

class ControllerTest extends TestCase
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

        Route::get('logs', [Controller::class, 'index']);

        File::makeDirectory($this->getBaseDir());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory($this->getBaseDir());
    }

    /** @test */
    public function it_shows_logs_page_when_there_are_no_log_files()
    {
        $this->visit('/logs')
            ->assertResponseOk()
            ->see('Laravel Log Viewer')
            ->see('Level')
            ->see('Context')
            ->see('Date')
            ->see('Content');
    }

    /** @test */
    public function it_shows_logs_page_when_there_are_log_files()
    {
        File::put($this->getBaseDir().'/laravel.log', '
[2017-01-04 05:22:25] production.ERROR: hello1 in Main.php:48
        ');
        File::put($this->getBaseDir().'/laravel2.log', '
[2017-01-04 05:22:25] production.ERROR: hello2 in Main.php:48
        ');
        File::makeDirectory($this->getBaseDir().'/directory1');

        $this->visit('/logs')
            ->assertResponseOk()
            ->see('laravel.log')
            ->see('laravel2.log')
            ->see('directory1')
            ->dontSee('hello1')
            ->dontSee('hello2')
            ->dontSee('Download file')
            ->dontSee('Delete file');
    }

    /** @test */
    public function it_shows_logs_page_with_selected_log_file()
    {
        File::put($this->getBaseDir().'/laravel.log', '
[2017-01-04 05:22:24] testing.INFO: hello1 in Main.php:48
        ');
        File::put($this->getBaseDir().'/laravel2.log', '
[2017-01-04 05:22:25] production.ERROR: hello2 in Main.php:48
        ');

        $this->visit('/logs?file='.base64_encode('laravel2.log'))
            ->assertResponseOk()
            ->see('laravel.log')
            ->see('laravel2.log')
            ->see('hello2')
            ->see('error')
            ->see('production')
            ->see('2017-01-04 05:22:25')
            ->see('Download file')
            ->see('Delete file')
            ->dontSee('hello1')
            ->dontSee('info')
            ->dontSee('testing')
            ->dontSee('2017-01-04 05:22:24');
    }

    /** @test */
    public function it_shows_logs_page_inside_specified_directory()
    {
        File::put($this->getBaseDir().'/laravel.log', '
[2017-01-04 05:22:24] testing.INFO: hello1 in Main.php:48
        ');
        File::put($this->getBaseDir().'/laravel2.log', '
[2017-01-04 05:22:25] production.ERROR: hello2 in Main.php:48
        ');
        File::makeDirectory($this->getBaseDir().'/directory1');
        File::put($this->getBaseDir().'/directory1/laravel3.log', '
[2017-01-04 05:22:25] production.ERROR: hello3 in Main.php:48
        ');

        $this->visit('/logs?dir='.base64_encode('directory1'))
            ->assertResponseOk()
            ->see('laravel3.log')
            ->dontSee('hello3')
            ->dontSee('Download file')
            ->dontSee('Delete file');
    }

    /** @test */
    public function it_shows_logs_page_with_selected_log_file_inside_specified_directory()
    {
        File::put($this->getBaseDir().'/laravel.log', '
[2017-01-04 05:22:24] testing.INFO: hello1 in Main.php:48
        ');
        File::put($this->getBaseDir().'/laravel2.log', '
[2017-01-04 05:22:25] production.ERROR: hello2 in Main.php:48
        ');
        File::makeDirectory($this->getBaseDir().'/directory1');
        File::put($this->getBaseDir().'/directory1/laravel3.log', '
[2017-01-04 05:22:26] production.ERROR: hello3 in Main.php:48
        ');

        $this->visit('/logs?file='.base64_encode('directory1/laravel3.log'))
            ->assertResponseOk()
            ->see('laravel3.log')
            ->see('hello3')
            ->see('error')
            ->see('production')
            ->see('2017-01-04 05:22:26')
            ->see('Download file')
            ->see('Delete file')
            ->dontSee('hello1')
            ->dontSee('hello2');
    }

    /** @test */
    public function it_deletes_specified_file()
    {
        File::makeDirectory($this->getBaseDir().'/directory1');
        File::put($this->getBaseDir().'/directory1/laravel3.log', '');

        $this->visit('/logs?file='.base64_encode('directory1/laravel3.log').'&delete')
            ->assertResponseOk()
            ->seePageIs('/logs');

        $this->assertFalse(File::exists($this->getBaseDir().'/directory1/laravel3.log'));
    }

    /** @test */
    public function it_downloads_specified_file()
    {
        File::makeDirectory($this->getBaseDir().'/directory1');
        File::put($this->getBaseDir().'/directory1/laravel3.log', '');

        $this->rawRequest('GET', '/logs?file='.base64_encode('directory1/laravel3.log').'&download')
            ->assertResponseOk()
            ->seeHeader('Content-Disposition', 'attachment; filename=laravel3.log');
    }

    /**
     * For file download responses.
     */
    private function rawRequest($method, $uri, $parameters = [], $cookies = [], $files = []): self
    {
        $uri = $this->prepareUrlForRequest($uri);
        $this->call($method, $uri, $parameters, $cookies, $files);
        $this->clearInputs()->followRedirects()->assertPageLoaded($uri);
        $this->currentUri = $this->app->make('request')->fullUrl();

        return $this;
    }

    /** @test */
    public function it_does_not_show_file_which_exceed_size_limit_in_config()
    {
        $this->app['config']->set('log-viewer.max_file_size', 10);
        File::put($this->getBaseDir().'/laravel3.log', str_repeat('A', 11));

        $this->visit('/logs?file='.base64_encode('laravel3.log'))
            ->assertResponseOk()
            ->see('File is too big, please download it.')
            ->see('Download file')
            ->see('Delete file');
    }

    /** @test */
    public function it_does_not_show_files_outside_base_directory()
    {
        File::put($this->getBaseDir().'/../some-secret-file', 'SECRET!!!');

        $this->visit('/logs?file='.base64_encode('../some-secret-file.php'))
            ->assertResponseOk()
            ->dontSee('SECRET!!!')
            ->see('You specified invalid path. Try go to the')
            ->dontSee('Download file')
            ->dontSee('Delete file')
            ->seeLink('beginning')
            ->click('beginning')
            ->seePageIs('/logs');

        File::delete($this->getBaseDir().'/../some-secret-file');
    }
}
