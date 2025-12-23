<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Server;

class GitHubService
{
    // Fallback credentials from env
    protected $defaultOwner;
    protected $defaultRepo;
    protected $defaultToken;

    public function __construct()
    {
        $this->defaultOwner = env('GITHUB_OWNER');
        $this->defaultRepo = env('GITHUB_REPO');
        $this->defaultToken = env('GITHUB_TOKEN');
    }

    /**
     * Helper to get effective credentials for a server
     */
    protected function getCredentials(Server $server = null)
    {
        return [
            'owner' => $server && $server->github_owner ? $server->github_owner : $this->defaultOwner,
            'repo' => $server && $server->github_repo ? $server->github_repo : $this->defaultRepo,
            'token' => $server && $server->github_token ? $server->github_token : $this->defaultToken,
        ];
    }

    public function triggerDeployment(Server $server, array $payload = [])
    {
        $creds = $this->getCredentials($server);
        if (!$creds['owner'] || !$creds['repo'] || !$creds['token']) {
             return [
                'status' => 'error',
                'message' => 'GitHub credentials missing for this server.'
            ];
        }

        $url = "https://api.github.com/repos/{$creds['owner']}/{$creds['repo']}/dispatches";

        // Merge server info into client_payload
        $data = [
            "event_type" => "custom-event",
            "client_payload" => array_merge([
                "server" => $server->name,
                "server_ip" => $server->ip_address,
                "ssh_key" => $server->ssh_key_path,
            ], $payload)
        ];

        $response = Http::withToken($creds['token'])
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

    // Workflow fetching methods usually need a context (repo). 
    // If the controller uses them generically, we might need to pass the Server or Repo details.
    // For now, assuming these might be used in a context where we know the repo, 
    // OR we change signatures to accept Server.
    
    // Let's update signatures to be robust, optionally accepting Server.
    public function getWorkflowRuns(Server $server = null)
    {
        $creds = $this->getCredentials($server);
        $url = "https://api.github.com/repos/{$creds['owner']}/{$creds['repo']}/actions/runs";
        
        $response = Http::withToken($creds['token'])
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

    public function getWorkflowRun($runId, Server $server = null)
    {
        $creds = $this->getCredentials($server);
        $url = "https://api.github.com/repos/{$creds['owner']}/{$creds['repo']}/actions/runs/{$runId}";
        
        $response = Http::withToken($creds['token'])
            ->accept('application/vnd.github.v3+json')
            ->get($url);
        return $response;
    }
    
    public function getWorkflowJobs($runId, Server $server = null)
    {
        $creds = $this->getCredentials($server);
        $url = "https://api.github.com/repos/{$creds['owner']}/{$creds['repo']}/actions/runs/{$runId}/jobs";
        
        return Http::withToken($creds['token'])
            ->accept('application/vnd.github.v3+json')
            ->get($url);
    }
}
