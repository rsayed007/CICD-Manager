<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Server;

class GitHubService
{
    protected $owner;
    protected $repo;
    protected $token;

    public function __construct()
    {
        $this->owner = env('GITHUB_OWNER');
        $this->repo = env('GITHUB_REPO');
        $this->token = env('GITHUB_TOKEN');
    }

    public function triggerDeployment(Server $server, array $payload = [])
    {
        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/dispatches";

        // Merge server info into client_payload
        $data = [
            "event_type" => "custom-event",
            "client_payload" => array_merge([
                "server" => $server->name,
                "server_ip" => $server->ip_address,
                "ssh_key" => $server->ssh_key_path,
                // We might need to send the directories/files list too if dynamic
                // "directories" => $server->directories->pluck('path'),
                // "files" => $server->deployFiles->pluck('path'),
            ], $payload)
        ];

        $response = Http::withToken($this->token)
            ->accept('application/vnd.github.v3+json')
            ->post($url, $data);

            if ($response->successful()) {
                $result['status'] = 'success';
                $result['messahe'] = "Dispatch event sent successfully!";
            } else {
                $result['status'] = 'error';
                $result['messahe'] = "Error: HTTP ".$response->status()."\n";
                $result['messahe'] .= $response->body();
            }
        return $result;
    }

    public function getWorkflowRuns()
    {
        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/actions/runs";
        
        $response = Http::withToken($this->token)
            ->accept('application/vnd.github.v3+json')
            ->get($url);

            if ($response->successful()) {
                $result['status'] = 'success';
                $result['messahe'] = "Runs fetched successfully!";
            } else {
                $result['status'] = 'error';
                $result['messahe'] = "Error: HTTP ".$response->status()."\n";
                $result['messahe'] .= $response->body();
            }
        return $result;
    }

    public function getWorkflowRun($runId)
    {
        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/actions/runs/{$runId}";
        
        return Http::withToken($this->token)
            ->accept('application/vnd.github.v3+json')
            ->get($url);
    }
    
    public function getWorkflowJobs($runId)
    {
        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/actions/runs/{$runId}/jobs";
        
        return Http::withToken($this->token)
            ->accept('application/vnd.github.v3+json')
            ->get($url);
    }
}
