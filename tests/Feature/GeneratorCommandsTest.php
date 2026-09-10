<?php

namespace Exert\Tests\Feature;

use Exert\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class GeneratorCommandsTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/exert-tests-'.bin2hex(random_bytes(8));
        $this->app['files']->makeDirectory($this->directory.'/app', 0755, true);
        // Generate only in this test's temporary application directory.
        $this->app->getNamespace();
        $this->app->useAppPath($this->directory.'/app');
    }

    protected function tearDown(): void
    {
        try {
            if (isset($this->directory)) { $this->app['files']->deleteDirectory($this->directory); }
        } finally {
            parent::tearDown();
        }
    }

    public function test_action_generation_uses_default_directory_and_new_template(): void
    {
        $this->artisan('make:action', ['name' => 'Users/CreateUser'])->assertSuccessful();
        $file = $this->directory.'/app/Http/Actions/Users/CreateUser.php';
        $this->assertFileExists($file);
        $content = file_get_contents($file);
        $this->assertStringContainsString('namespace App\Http\Actions\Users;', $content);
        $this->assertStringContainsString('use Exert\Action;', $content);
        $this->assertStringContainsString("protected array \$methods = ['GET'];", $content);
        token_get_all($content, TOKEN_PARSE);
    }

    public function test_custom_directory_overwrite_protection_and_force(): void
    {
        config(['exert.actions_path' => 'Domain/Actions']);
        Artisan::call('make:action', ['name' => 'CreateUser']);
        $file = $this->directory.'/app/Domain/Actions/CreateUser.php';
        $this->assertStringContainsString('namespace App\Domain\Actions;', file_get_contents($file));
        file_put_contents($file, '<?php // preserve');
        Artisan::call('make:action', ['name' => 'CreateUser']);
        $this->assertSame('<?php // preserve', file_get_contents($file));
        Artisan::call('make:action', ['name' => 'CreateUser', '--force' => true]);
        $this->assertStringContainsString('class CreateUser extends Action', file_get_contents($file));
    }

    public function test_controller_generation_and_invalid_action_directory(): void
    {
        $this->artisan('make:action-controller', ['name' => 'Admin/UserController'])->assertSuccessful();
        $file = $this->directory.'/app/Http/Controllers/Admin/UserController.php';
        $this->assertStringContainsString('use Exert\ActionController;', file_get_contents($file));
        config(['exert.actions_path' => '../outside']);
        $this->expectException(\InvalidArgumentException::class);
        Artisan::call('make:action', ['name' => 'Invalid']);
    }
}
