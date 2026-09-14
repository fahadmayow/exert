<?php

namespace Exert\Tests\Feature;

use Exert\Tests\TestCase;

class MessagesCommandTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/exert-messages-'.bin2hex(random_bytes(8));
        $this->app['files']->makeDirectory($this->directory, 0755, true);
        $this->app->useConfigPath($this->directory);
    }

    protected function tearDown(): void
    {
        try {
            if (isset($this->directory)) {
                $this->app['files']->deleteDirectory($this->directory);
            }
        } finally {
            parent::tearDown();
        }
    }

    public function test_it_creates_the_messages_configuration(): void
    {
        $this->artisan('exert:messages')->assertSuccessful();

        $file = $this->directory.'/messages.php';
        $this->assertFileExists($file);

        $config = require $file;
        $this->assertSame('OK', $config['200']['status']);
        $this->assertSame(404, $config['404']['code']);
        $this->assertSame('NOT FOUND', $config['404']['status']);
    }

    public function test_it_does_not_overwrite_without_force(): void
    {
        $file = $this->directory.'/messages.php';
        $this->app['files']->put($file, '<?php return [];');

        $this->artisan('exert:messages')->assertFailed();

        $this->assertSame('<?php return [];', $this->app['files']->get($file));
    }

    public function test_it_overwrites_with_force(): void
    {
        $file = $this->directory.'/messages.php';
        $this->app['files']->put($file, '<?php return [];');

        $this->artisan('exert:messages', ['--force' => true])->assertSuccessful();

        $config = require $file;
        $this->assertSame('OK', $config['200']['status']);
    }
}
