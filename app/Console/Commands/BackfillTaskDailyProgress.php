<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\MaterialItem;
use App\Models\Project;
use App\Models\TaskCompletion;
use App\Models\TaskDailyProgress;
use App\Support\MaterialReport;
use App\Support\SCurve;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Memulihkan riwayat progress harian (task_daily_progress) dari bukti yang masih tersimpan di database,
 * sebagai bahan garis AKTUAL S-Curve. Aman dijalankan berulang (baris yang sudah ada tidak ditimpa).
 *
 * Sumber, urut prioritas:
 *   1. Rekam Jejak (activity_logs)  : nilai lama -> baru tiap penyimpanan form, lengkap dengan waktunya
 *   2. Tanggal selesai jobdesk      : task_completions (jobdesk yang mencapai 100%)
 *   3. Kolom "Tanggal Update"       : titik terakhir tiap jobdesk (nilai saat ini pada tanggal itu)
 *   4. Raw Material                 : dihitung ulang dari tanggal & jumlah kedatangan barang (material_arrivals)
 */
class BackfillTaskDailyProgress extends Command
{
    protected $signature = 'scurve:backfill {--dry-run : Hanya hitung & tampilkan perkiraan, jangan simpan}';

    protected $description = 'Pulihkan riwayat progress harian (bahan garis aktual S-Curve) dari Rekam Jejak, tanggal selesai, Tanggal Update, dan kedatangan material';

    private const RAW_MATERIAL = 'task_main_material';

    private bool $dry = false;

    /** @var array<string, bool> baris yang dibuat oleh proses ini: "projectId|taskKey|tanggal" */
    private array $made = [];

    public function handle(): int
    {
        $this->dry = (bool) $this->option('dry-run');

        $counts = [
            'rekam_jejak' => $this->fromActivityLogs(),
            'selesai'     => $this->fromCompletions(),
            'tgl_update'  => $this->fromLastUpdateDates(),
            'material'    => $this->fromMaterialArrivals(),
        ];

        $prefix = $this->dry ? '[dry-run, perkiraan] ' : '';
        $this->info($prefix . 'Rekam Jejak       : ' . $counts['rekam_jejak'] . ' baris');
        $this->info($prefix . 'Tanggal selesai   : ' . $counts['selesai'] . ' baris');
        $this->info($prefix . 'Kolom Tgl Update  : ' . $counts['tgl_update'] . ' baris');
        $this->info($prefix . 'Kedatangan material: ' . $counts['material'] . ' baris');
        $this->info($prefix . 'Total ' . ($this->dry ? 'yang akan ditambahkan' : 'ditambahkan') . ': ' . array_sum($counts) . ' baris riwayat.');

        return self::SUCCESS;
    }

    private function exists(int $projectId, string $taskKey, string $date): bool
    {
        return isset($this->made["{$projectId}|{$taskKey}|{$date}"])
            || TaskDailyProgress::where('project_id', $projectId)->where('task_key', $taskKey)->whereDate('log_date', $date)->exists();
    }

    private function hasLater(int $projectId, string $taskKey, string $date): bool
    {
        return TaskDailyProgress::where('project_id', $projectId)->where('task_key', $taskKey)->whereDate('log_date', '>', $date)->exists();
    }

    private function create(int $projectId, string $taskKey, int $progress, string $date, $userId = null): void
    {
        if (!$this->dry) {
            TaskDailyProgress::create([
                'project_id' => $projectId,
                'task_key'   => $taskKey,
                'progress'   => max(0, min(100, $progress)),
                'log_date'   => $date,
                'user_id'    => $userId,
            ]);
        }
        $this->made["{$projectId}|{$taskKey}|{$date}"] = true;
    }

    // 1. Rekam Jejak
    private function fromActivityLogs(): int
    {
        $inserted = 0;
        $seen     = [];

        $logs = ActivityLog::whereNotNull('project_id')->whereNotNull('new_values')->orderBy('created_at')->orderBy('id')->get();

        foreach ($logs as $log) {
            $new = (array) $log->new_values;
            $old = (array) ($log->old_values ?? []);
            $day = SCurve::dateOf($log->created_at);

            foreach ($new as $key => $value) {
                // Hanya kolom progress jobdesk (task_xxx) berupa angka. Raw Material dipulihkan dari kedatangan material (langkah 4).
                if (strpos((string) $key, 'task_') !== 0 || !is_numeric($value) || $key === self::RAW_MATERIAL) {
                    continue;
                }

                $progress = max(0, min(100, (int) round((float) $value)));
                $marker   = $log->project_id . '|' . $key;

                // Titik awal: nilai SEBELUM perubahan pertama (sehari sebelumnya), kalau sudah > 0
                if (!isset($seen[$marker])) {
                    $seen[$marker] = true;
                    $before = isset($old[$key]) && is_numeric($old[$key]) ? (int) round((float) $old[$key]) : 0;
                    if ($before > 0) {
                        $beforeDay = Carbon::parse($day)->subDay()->toDateString();
                        if (!$this->exists($log->project_id, $key, $beforeDay)) {
                            $this->create($log->project_id, $key, $before, $beforeDay);
                            $inserted++;
                        }
                    }
                }

                // Perubahan lain di hari yang sama yang dibuat proses ini: nilai TERAKHIR yang dipakai
                $dayKey = "{$log->project_id}|{$key}|{$day}";
                if (isset($this->made[$dayKey])) {
                    if (!$this->dry) {
                        TaskDailyProgress::where('project_id', $log->project_id)->where('task_key', $key)->whereDate('log_date', $day)->update(['progress' => $progress]);
                    }
                    continue;
                }

                // Baris yang sudah ada (dari pencatatan otomatis / koreksi manual) tidak ditimpa
                if ($this->exists($log->project_id, $key, $day)) {
                    continue;
                }

                $this->create($log->project_id, $key, $progress, $day, $log->user_id);
                $inserted++;
            }
        }

        return $inserted;
    }

    // 2. Tanggal selesai jobdesk (mencapai 100%)
    private function fromCompletions(): int
    {
        $inserted = 0;

        foreach (TaskCompletion::orderBy('completed_at')->get() as $completion) {
            if ($completion->task_key === self::RAW_MATERIAL) {
                continue;
            }

            $date = SCurve::dateOf($completion->completed_at);
            if ($this->exists($completion->project_id, $completion->task_key, $date)) {
                continue;
            }

            $this->create($completion->project_id, $completion->task_key, 100, $date, $completion->user_id);
            $inserted++;
        }

        return $inserted;
    }

    // 3. Kolom "Tanggal Update": nilai jobdesk saat ini dianggap tercapai pada tanggal itu
    private function fromLastUpdateDates(): int
    {
        $inserted = 0;
        $today    = SCurve::today()->toDateString();
        $keys     = array_keys(SCurve::taskLabels());

        foreach (Project::all() as $project) {
            foreach ($keys as $taskKey) {
                if ($taskKey === self::RAW_MATERIAL) {
                    continue;
                }

                $updated = $project->getAttribute('last_update_' . $taskKey);
                if (!$updated) {
                    continue;
                }

                $date  = Carbon::parse($updated)->toDateString();
                $value = min(100, max(0, (int) $project->$taskKey));

                // Lewati kalau masa depan, nilai 0, sudah ada baris di tanggal itu, atau sudah ada catatan yang lebih baru
                if ($date > $today || $value <= 0
                    || $this->exists($project->id, $taskKey, $date)
                    || $this->hasLater($project->id, $taskKey, $date)) {
                    continue;
                }

                $this->create($project->id, $taskKey, $value, $date);
                $inserted++;
            }
        }

        return $inserted;
    }

    // 4. Raw Material: persen kumulatif dari kedatangan barang pada tiap tanggal kedatangan
    private function fromMaterialArrivals(): int
    {
        $inserted = 0;

        $itemsByProject = MaterialItem::with('arrivals')->get()->groupBy('project_id');

        foreach ($itemsByProject as $projectId => $items) {
            $total = (float) $items->sum('quantity');
            if ($total <= 0) {
                continue;
            }

            $dates = $items->flatMap(fn($item) => $item->arrivals->map(fn($a) => Carbon::parse($a->arrival_date)->toDateString()))
                ->unique()->sort()->values();

            foreach ($dates as $date) {
                $fulfilled = 0.0;
                foreach ($items as $item) {
                    $arrived    = (float) $item->arrivals->filter(fn($a) => Carbon::parse($a->arrival_date)->toDateString() <= $date)->sum('quantity');
                    $fulfilled += min($arrived, (float) $item->quantity);
                }

                $progress = MaterialReport::progressFromPercentage(min(100.0, $fulfilled / $total * 100));

                if ($this->exists((int) $projectId, self::RAW_MATERIAL, $date)) {
                    continue;
                }

                $this->create((int) $projectId, self::RAW_MATERIAL, $progress, $date);
                $inserted++;
            }
        }

        return $inserted;
    }
}