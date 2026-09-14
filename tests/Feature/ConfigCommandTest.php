<?php

namespace Exert\Tests\Feature;

use Exert\Tests\TestCase;

class ConfigCommandTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/exert-config-'.bin2hex(random_bytes(8));
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

    public function test_it_creates_the_exert_configuration(): void
    {
        $this->artisan('exert:config')->assertSuccessful();

        $file = $this->directory.'/exert.php';
        $this->assertFileExists($file);

        $config = require $file;
        $this->assertSame('action', $config['action_key']);
        $this->assertSame('query', $config['action_source']);
        $this->assertSame('Http/Actions', $config['actions_path']);
    }

    public function test_it_does_not_overwrite_without_force(): void
    {
        $file = $this->directory.'/exert.php';
        $this->app['files']->put($file, '<?php return [];');

        $this->artisan('exert:config')->assertFailed();

        $this->assertSame('<?php return [];', $this->app['files']->get($file));
    }

    public function test_it_overwrites_with_force(): void
    {
        $file = $this->directory.'/exert.php';
        $this->app['files']->put($file, '<?php return [];');

        $this->artisan('exert:config', ['--force' => true])->assertSuccessful();

        $config = require $file;
        $this->assertSame('action', $config['action_key']);
    }
}
