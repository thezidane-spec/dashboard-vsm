<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskProgress extends Model
{
    use HasFactory;

    protected $table = 'task_progress';

    protected $fillable = [
        'project_id',
        'started_by',
        'task_key',
        'department',
        'started_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function startedBy()
    {
        return $this->belongsTo(User::class, 'started_by');
    }
}