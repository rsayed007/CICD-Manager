<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Server;
use App\Services\GitHubService;
use Mockery;

use App\Models\User;

class CicdBotTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_dashboard()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('Deployment Servers');
    }

    public function test_can_create_server()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/servers', [
            'name' => 'Test Server',
            'ip_address' => '127.0.0.1',
            'username' => 'root',
            'deploy_path' => '/var/www/test',
            'github_token' => 'ghp_secretToken',
            'github_owner' => 'owner',
            'github_repo' => 'repo',
            'is_active' => 'on',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('servers', [
            'name' => 'Test Server',
            'github_token' => 'ghp_secretToken',
        ]);
    }

    public function test_can_update_config()
    {
        $user = User::factory()->create();
        $server = Server::create([
            'name' => 'Test Server',
            'ip_address' => '127.0.0.1',
            'username' => 'root',
            'deploy_path' => '/var/www/test',
        ]);

        $response = $this->actingAs($user)->post("/servers/{$server->id}/config", [
            'directories' => ['src/', 'config/'],
            'files_list' => ['readme.md'],
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('directories', ['path' => 'src/']);
        $this->assertDatabaseHas('deploy_files', ['path' => 'readme.md']);
    }

    public function test_can_simulate_deployment()
    {
        $user = User::factory()->create();
        $server = Server::create([
            'name' => 'Sim Server',
            'ip_address' => '1.2.3.4',
            'username' => 'user',
            'deploy_path' => '/dest',
        ]);
        
        $server->directories()->create(['path' => 'dir1']);

        $response = $this->actingAs($user)->get("/servers/{$server->id}/simulate");
        
        $response->assertStatus(200);
        $json = $response->json();
        $this->assertStringContainsString('# Simulation for Sim Server', $json['simulation']);
        $this->assertStringContainsString('rsync -avR "dir1" user@1.2.3.4:"/dest/"', $json['simulation']);
    }

    public function test_can_update_server_details()
    {
        $user = User::factory()->create();
        $server = Server::create([
            'name' => 'Old Name',
            'ip_address' => '1.1.1.1',
            'username' => 'user',
            'deploy_path' => '/var/www',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put(route('servers.update', $server), [
            'name' => 'New Name',
            'ip_address' => '1.1.1.1',
            'username' => 'user',
            'deploy_path' => '/var/www',
            'github_token' => 'new-token',
            'is_active' => 'on',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('servers', [
            'id' => $server->id,
            'name' => 'New Name',
            'github_token' => 'new-token',
            'is_active' => true,
        ]);
    }
}
