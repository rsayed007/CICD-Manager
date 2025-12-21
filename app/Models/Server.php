<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $fillable = ['name', 'ip_address', 'username', 'deploy_path', 'ssh_key_path'];

    public function directories()
    {
        return $this->hasMany(Directory::class);
    }

    public function deployFiles()
    {
        return $this->hasMany(DeployFile::class);
    }

    public function workflowLogs()
    {
        return $this->hasMany(WorkflowLog::class);
    }
}
