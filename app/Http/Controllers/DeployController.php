<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Directory;
use App\Models\DeployFile;
use App\Models\WorkflowLog;
use App\Services\GitHubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeployController extends Controller
{
    protected $github;

    public function __construct(GitHubService $github)
    {
        $this->github = $github;
    }

    // Dashboard: List servers
    public function index()
    {
        $servers = Server::all();
        return view('dashboard', compact('servers'));
    }

    // Server Details: Config & History
    public function show(Server $server)
    {
        $server->load(['directories', 'deployFiles', 'workflowLogs' => function($q) {
            $q->latest()->limit(10);
        }]);
        
        return view('server.show', compact('server'));
    }

    // Store new server
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'ip_address' => 'required',
            'username' => 'required',
            'deploy_path' => 'required',
            'ssh_key_path' => 'nullable',
        ]);

        Server::create($data);
        return redirect()->back()->with('success', 'Server added successfully.');
    }

    // Update Server Config (Dirs/Files)
    public function updateConfig(Request $request, Server $server)
    {
        // specific logic to update directories and files
        // expecting arrays: directories[], files[]
        
        $server->directories()->delete();
        $server->deployFiles()->delete();

        if ($request->has('directories')) {
            foreach ($request->directories as $path) {
                if(!empty($path)) {
                    $server->directories()->create(['path' => $path]);
                }
            }
        }

        if ($request->has('files_list')) {
            foreach ($request->files_list as $path) { // 'files' is reserved in request for uploads
                 if(!empty($path)) {
                    $server->deployFiles()->create(['path' => $path]);
                }
            }
        }

        return redirect()->back()->with('success', 'Configuration updated.');
    }

    // Trigger Deployment
    public function trigger(Server $server)
    {
        // 1. Prepare Payload
        // We might want to pass dynamic config, but usually the workflow reads from DB or we pass it.
        // The user's workflow read from yq deploy_map.yml. 
        // We can pass the config as JSON in client_payload so the Action can parse it directly 
        // instead of reading a file! That's a huge improvement.
        
        $dirs = $server->directories->pluck('path')->toArray();
        $files = $server->deployFiles->pluck('path')->toArray();

        $payload = [
            'directories' => $dirs,
            'files' => $files,
            'target_dir' => $server->deploy_path, // Pass target dir to action
            'username' => $server->username,
            'ip' => $server->ip_address
        ];


        try {
            // Trigger via Service
            $result = $this->github->triggerDeployment($server, $payload);

            // Log it
            // $server->workflowLogs()->create([
            //     'status' => 'pending',
            //     'logs' => 'Deployment triggered via GitHub API.',
            // ]);
            
            if ($result['status'] == 'success') {
                return redirect()->back()->with('success', 'Deployment triggered!');
            } else {
                return redirect()->back()->with('error', 'Failed to trigger deployment: ' . $result['message']);
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to trigger deployment: ' . $e->getMessage());
        }
    }

    // Simulate
    public function simulate(Server $server)
    {
        $output = "# Simulation for " . $server->name . "\n";
        $output .= "# Target: " . $server->username . "@" . $server->ip_address . ":" . $server->deploy_path . "\n\n";

        foreach ($server->directories as $dir) {
             $output .= "rsync -avR \"{$dir->path}\" {$server->username}@{$server->ip_address}:\"{$server->deploy_path}/\"\n";
        }

        foreach ($server->deployFiles as $file) {
             $output .= "rsync -avR \"{$file->path}\" {$server->username}@{$server->ip_address}:\"{$server->deploy_path}/\"\n";
        }

        return response()->json(['simulation' => $output]);
    }
}
