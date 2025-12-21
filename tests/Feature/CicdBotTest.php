<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Server;
use App\Services\GitHubService;
use Mockery;

class CicdBotTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_dashboard()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Deployment Servers');
    }

    public function test_can_create_server()
    {
        $response = $this->post('/servers', [
            'name' => 'Test Server',
            'ip_address' => '127.0.0.1',
            'username' => 'root',
            'deploy_path' => '/var/www/test',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('servers', ['name' => 'Test Server']);
    }

    public function test_can_update_config()
    {
        $server = Server::create([
            'name' => 'Test Server',
            'ip_address' => '127.0.0.1',
            'username' => 'root',
            'deploy_path' => '/var/www/test',
        ]);

        $response = $this->post("/servers/{$server->id}/config", [
            'directories' => ['src/', 'config/'],
            'files_list' => ['readme.md'],
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('directories', ['path' => 'src/']);
        $this->assertDatabaseHas('deploy_files', ['path' => 'readme.md']);
    }

    public function test_can_simulate_deployment()
    {
        $server = Server::create([
            'name' => 'Test Server',
            'ip_address' => '1.2.3.4',
            'username' => 'user',
            'deploy_path' => '/dest',
        ]);
        
        $server->directories()->create(['path' => 'dir1']);

        $response = $this->get("/servers/{$server->id}/simulate");
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['simulation' => "# Simulation for Test Server\n# Target: user@1.2.3.4:/dest\n\nrsync -avR \"dir1\" user@1.2.3.4:\"/dest/\"\n"]);
    }
}
