<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Directory extends Model
{
    protected $fillable = ['server_id', 'path'];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}
