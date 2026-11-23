<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskDailyProgress extends Model
{
    use HasFactory;

    protected $table = 'task_daily_progress';

    protected $fillable = [
        'project_id',
        'task_key',
        'progress',
        'log_date',
        'user_id',
    ];

    protected $casts = [
        'progress' => 'integer',
        'log_date' => 'date',
    ];

    /**
     * Catat progress sebuah jobdesk pada TANGGAL tertentu.
     * Kalau tanggal itu sudah ada barisnya, nilainya diperbarui (nilai terakhir hari itu yang dipakai).
     */
    public static function logOn($projectId, string $taskKey, $progress, $date, $userId = null): self
    {
        $date     = \Carbon\Carbon::parse($date)->toDateString();
        $progress = max(0, min(100, (int) round($progress)));

        // Cari baris tanggal itu dengan whereDate supaya cocok di semua jenis database
        // (kolom date di MySQL, atau teks bertanda jam di SQLite)
        $row = static::where('project_id', $projectId)
            ->where('task_key', $taskKey)
            ->whereDate('log_date', $date)
            ->first();

        if ($row) {
            $row->update(['progress' => $progress, 'user_id' => $userId]);

            return $row;
        }

        return static::create([
            'project_id' => $projectId,
            'task_key'   => $taskKey,
            'log_date'   => $date,
            'progress'   => $progress,
            'user_id'    => $userId,
        ]);
    }

    // Catat progress sebuah jobdesk untuk HARI INI
    public static function logToday($projectId, string $taskKey, $progress, $userId = null): self
    {
        return static::logOn($projectId, $taskKey, $progress, \App\Support\SCurve::today()->toDateString(), $userId);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}