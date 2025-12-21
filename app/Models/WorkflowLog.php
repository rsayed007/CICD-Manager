<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowLog extends Model
{
    protected $fillable = ['server_id', 'workflow_run_id', 'status', 'logs'];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}
