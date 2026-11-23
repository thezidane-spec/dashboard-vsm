<?php

namespace App\Support;

use App\Models\Project;
use App\Models\TaskDailyProgress;
use Carbon\Carbon;

/**
 * Menyusun data S-Curve satu proyek: garis RENCANA vs garis AKTUAL.
 *
 * RENCANA
 *   Tiap divisi punya pasangan Due Date + Planning (%) = target persen divisi itu pada Due Date-nya.
 *   Kurva divisi: dari tanggal mulai proyek (0%) naik berbentuk S ringan sampai Planning (%) pada Due Date divisi,
 *   lalu datar. Kurva proyek = jumlah kurva tiap divisi x bobot divisi (total bobot 100).
 *   Due Date divisi yang kosong memakai Due Date proyek.
 *   Production memakai target per TAHAP (Preparation & Main Production); kalau kedua tahap belum diisi,
 *   dipakai target divisi Production.
 *
 * AKTUAL
 *   Dari riwayat harian per jobdesk (task_daily_progress) x bobot jobdesk (sama dengan Project::actual_progress).
 *   Garis aktual berhenti di HARI INI dan titik terakhirnya selalu sama dengan Actual Progress proyek saat ini.
 *   Garis aktual TIDAK PERNAH TURUN: kalau persen jobdesk diturunkan, titik tanggal-tanggal sebelumnya yang lebih
 *   tinggi dipotong mengikuti nilai tertinggi yang sekarang berlaku, jadi kurva tetap mendatar lalu naik.
 *
 * REMARK PROBLEM
 *   Remarks Problem proyek (tanggal + teks) ditampilkan sebagai penanda di kurva beserta Planning total (%) dan
 *   Aktual (%) pada tanggal masalah itu.
 *
 * SUMBU WAKTU
 *   Dari tanggal mulai sampai tanggal paling akhir antara Due Date (proyek & divisi), hari ini, dan log terakhir,
 *   jadi progress yang terlambat melewati Due Date tetap terlihat.
 */
class SCurve
{
    // Aplikasi berjalan dengan zona waktu UTC; tanggal riwayat progress memakai WIB supaya update pagi hari
    // (00.00-07.00 WIB) tidak tercatat di hari sebelumnya.
    public const TIMEZONE = 'Asia/Jakarta';

    // Tanggal HARI INI menurut WIB, sebagai tengah malam pada zona waktu aplikasi (konsisten dengan tanggal lain)
    public static function today(): Carbon
    {
        return Carbon::parse(Carbon::now(self::TIMEZONE)->toDateString())->startOfDay();
    }

    // Tanggal (WIB) dari sebuah timestamp yang tersimpan di database (UTC)
    public static function dateOf($timestamp): string
    {
        return Carbon::parse($timestamp)->copy()->setTimezone(self::TIMEZONE)->toDateString();
    }

    // Tanggal mulai proyek: contract_review, kalau kosong tanggal proyek dibuat
    private static function startDate(Project $project): Carbon
    {
        if ($project->contract_review) {
            return Carbon::parse($project->contract_review)->startOfDay();
        }

        return ($project->created_at ?? Carbon::today())->copy()->startOfDay();
    }

    // Selisih hari bertanda (b - a), dihitung dari timestamp supaya aman di versi Carbon manapun
    private static function days(Carbon $a, Carbon $b): int
    {
        return (int) round(($b->getTimestamp() - $a->getTimestamp()) / 86400);
    }

    // Satu tahap Production: pakai target tahap; kalau tahap belum diisi sama sekali, pakai target divisi Production
    private static function stageTarget(Project $project, string $stageKey): array
    {
        $plan = (int) $project->{"plan_{$stageKey}"};
        $due  = $project->{"due_date_{$stageKey}"};

        if (!$due && $plan === 0) {
            return [(int) $project->plan_production, $project->due_date_production];
        }

        return [$plan, $due];
    }

    /**
     * Divisi untuk kurva rencana.
     *
     * @return array<int, array{label: string, weight: float, plan: int, due: ?Carbon}>
     */
    public static function divisions(Project $project): array
    {
        [$prepPlan, $prepDue] = self::stageTarget($project, 'preparation');
        [$mainPlan, $mainDue] = self::stageTarget($project, 'main_production');

        $stageWeight = fn(string $key) => array_sum(Project::$productionStages[$key]['tasks']);

        $rows = [
            ['label' => 'Engineering',                  'weight' => array_sum(Project::$engineeringTasks),  'plan' => (int) $project->plan_engineering, 'due' => $project->due_date_engineering],
            ['label' => 'Procurement',                  'weight' => array_sum(Project::$procurementTasks),  'plan' => (int) $project->plan_procurement, 'due' => $project->due_date_procurement],
            ['label' => 'PPIC',                         'weight' => array_sum(Project::$ppicTasks),         'plan' => (int) $project->plan_ppic,         'due' => $project->due_date_ppic],
            ['label' => 'Production - Preparation',     'weight' => $stageWeight('preparation'),            'plan' => $prepPlan,                         'due' => $prepDue],
            ['label' => 'Production - Main Production', 'weight' => $stageWeight('main_production'),        'plan' => $mainPlan,                         'due' => $mainDue],
            ['label' => 'Finish Process',                'weight' => array_sum(Project::$qcTasks),           'plan' => (int) $project->plan_qc,           'due' => $project->due_date_qc],
            ['label' => 'QA/QC',                        'weight' => Project::QAQC_TOTAL,         'plan' => (int) $project->plan_qaqc,         'due' => $project->due_date_qaqc],
        ];

        $projectDue = $project->due_date ? Carbon::parse($project->due_date)->startOfDay() : null;

        foreach ($rows as &$row) {
            $due        = $row['due'] ? Carbon::parse($row['due'])->startOfDay() : $projectDue;
            $row['due'] = $due;
        }
        unset($row);

        return $rows;
    }

    // Bentuk S ringan: campuran garis lurus dan smoothstep (x = 0..1)
    private static function shape(float $x): float
    {
        return 0.5 * $x + 0.5 * ($x * $x * (3 - 2 * $x));
    }

    /**
     * Semua jobdesk beserta labelnya ("Divisi - Nama"), dipakai untuk form koreksi riwayat & validasi.
     *
     * @return array<string, string> [task_key => label]
     */
    public static function taskLabels(): array
    {
        $production = [];
        foreach (Project::$productionStages as $stage) {
            $production += $stage['tasks'];
        }

        $groups = [
            'Engineering' => [Project::$engineeringTasks, Project::$engineeringTaskInfo],
            'Procurement' => [Project::$procurementTasks, Project::$procurementTaskInfo],
            'PPIC'        => [Project::$ppicTasks,        Project::$ppicTaskInfo],
            'Production'  => [$production,                Project::$productionTaskInfo],
            'Finish Process' => [Project::$qcTasks,          Project::$qcTaskInfo],
            'QA/QC'       => [Project::$qaqcTasks,        Project::$qaqcTaskInfo],
        ];

        $labels = [];
        foreach ($groups as $division => [$tasks, $info]) {
            foreach ($tasks as $taskKey => $weight) {
                $name = $info[$taskKey]['name'] ?? ($info[$taskKey]['label'] ?? ucwords(str_replace('_', ' ', substr($taskKey, 5))));
                $labels[$taskKey] = $division . ' - ' . $name;
            }
        }

        return $labels;
    }

    // Bobot semua jobdesk yang ikut dihitung (sama dengan Project::actual_progress)
    private static function taskWeights(Project $project): array
    {
        $weights = $project->activeEngineeringTasks();

        foreach (['preparation', 'main_production', 'ppic', 'finishing', 'qaqc'] as $division) {
            $weights += $project->activeTasks($division);
        }

        return $weights + Project::$procurementTasks;
    }

    public static function build(Project $project, ?Carbon $today = null): array
    {
        $today = ($today ?? self::today())->copy()->startOfDay();
        $start = self::startDate($project);
        $divisions  = self::divisions($project);
        $projectDue = $project->due_date ? Carbon::parse($project->due_date)->startOfDay() : null;

        // ---------- Riwayat harian aktual ----------
        $byTask  = [];
        $lastLog = null;
        $logs = TaskDailyProgress::where('project_id', $project->id)->orderBy('log_date')->get(['task_key', 'progress', 'log_date']);
        foreach ($logs as $log) {
            $dateStr = Carbon::parse($log->log_date)->format('Y-m-d');
            $byTask[$log->task_key][] = [$dateStr, (int) $log->progress];
            $lastLog = $dateStr;
        }

        // ---------- Sumbu waktu ----------
        $end = $today->copy();
        if ($projectDue && $projectDue->gt($end)) {
            $end = $projectDue->copy();
        }
        foreach ($divisions as $division) {
            if ($division['due'] && $division['due']->gt($end)) {
                $end = $division['due']->copy();
            }
        }
        if ($lastLog && Carbon::parse($lastLog)->gt($end)) {
            $end = Carbon::parse($lastLog)->startOfDay();
        }
        if ($end->lte($start)) {
            $end = $start->copy()->addDay();
        }

        $span = self::days($start, $end);
        $step = max(1, (int) ceil($span / 240));

        $dates = [];
        for ($i = 0; $i <= $span; $i += $step) {
            $dates[$start->copy()->addDays($i)->format('Y-m-d')] = true;
        }
        $dates[$end->format('Y-m-d')] = true;
        if ($today->gte($start)) {
            $dates[$today->format('Y-m-d')] = true;
        }
        if ($projectDue && $projectDue->gte($start)) {
            $dates[$projectDue->format('Y-m-d')] = true;
        }
        // Tanggal Remarks Problem yang jatuh di dalam rentang kurva ikut jadi titik sumbu
        $problemDates = [];
        foreach ((array) ($project->remarks_problems ?? []) as $problem) {
            $iso = null;
            if (!empty($problem['date'])) {
                try { $iso = Carbon::parse($problem['date'])->format('Y-m-d'); } catch (\Throwable $e) { $iso = null; }
            }
            $problemDates[] = $iso;
            if ($iso && $iso >= $start->format('Y-m-d') && $iso <= $end->format('Y-m-d')) {
                $dates[$iso] = true;
            }
        }
        $labels = array_keys($dates);
        sort($labels);

        // ---------- Rencana ----------
        $planAt = function (Carbon $t) use ($divisions, $start) {
            $total = 0.0;
            foreach ($divisions as $division) {
                $planDays = $division['due'] ? self::days($start, $division['due']) : 0;
                $x = $planDays <= 0 ? 1.0 : max(0.0, min(1.0, self::days($start, $t) / $planDays));
                $total += $division['weight'] * ($division['plan'] / 100) * self::shape($x);
            }

            return $total;
        };

        // ---------- Aktual ----------
        $weights  = self::taskWeights($project);
        $todayStr = $today->format('Y-m-d');

        $actualAt = function (string $dateStr) use ($weights, $byTask, $project, $todayStr) {
            $total = 0.0;
            foreach ($weights as $taskKey => $weight) {
                if ($dateStr === $todayStr) {
                    // Titik hari ini = nilai saat ini (sama dengan Actual Progress proyek)
                    $value = min(100, max(0, (int) $project->$taskKey));
                } else {
                    $value = 0;
                    foreach ($byTask[$taskKey] ?? [] as [$logDate, $logValue]) {
                        if ($logDate <= $dateStr) {
                            $value = $logValue;
                        } else {
                            break;
                        }
                    }
                }
                $total += $weight * $value / 100;
            }

            return $total;
        };

        $planSeries   = [];
        $actualSeries = [];
        $finishedOn   = null;

        foreach ($labels as $dateStr) {
            $planSeries[]   = round($planAt(Carbon::parse($dateStr)), 1);
            $actualSeries[] = $dateStr <= $todayStr ? round($actualAt($dateStr), 1) : null;
        }

        // Kurva aktual tidak boleh turun: dari kanan (hari ini) ke kiri, tiap titik dibatasi nilai titik sesudahnya.
        // Jadi kalau persen diturunkan, tanggal-tanggal sebelumnya yang lebih tinggi mengikuti nilai tertinggi sekarang.
        $running = null;
        for ($i = count($actualSeries) - 1; $i >= 0; $i--) {
            if ($actualSeries[$i] === null) {
                continue;
            }
            $running         = $running === null ? $actualSeries[$i] : min($running, $actualSeries[$i]);
            $actualSeries[$i] = $running;
        }

        foreach ($labels as $i => $dateStr) {
            if ($finishedOn === null && $actualSeries[$i] !== null && $actualSeries[$i] >= 99.5) {
                $finishedOn = $dateStr;
            }
        }

        $planToday   = round($planAt($today), 1);
        $actualToday = round($actualAt($todayStr), 1);

        // ---------- Remarks Problem (penanda di kurva) ----------
        $labelIndex = array_flip($labels);
        $remarks    = [];
        foreach (array_values((array) ($project->remarks_problems ?? [])) as $n => $problem) {
            $text = trim((string) ($problem['text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $iso = $problemDates[$n] ?? null;
            $idx = ($iso !== null && isset($labelIndex[$iso])) ? $labelIndex[$iso] : null;

            $remarks[] = [
                'no'      => count($remarks) + 1,
                'date'    => $iso,
                'text'    => $text,
                'plan'    => $iso ? $project->planProgressOn($iso) : null,
                'actual'  => ($idx !== null && $actualSeries[$idx] !== null) ? $actualSeries[$idx] : null,
                'index'   => $idx,
            ];
        }

        // ---------- Status ----------
        $statusType = 'primary';
        if ($actualToday >= 99.5) {
            $statusType = 'success';
            $statusText = 'Selesai';
            if ($projectDue && $finishedOn) {
                $diff = self::days($projectDue, Carbon::parse($finishedOn));
                $statusText .= $diff < 0 ? ' (' . abs($diff) . ' hari lebih awal dari due date)'
                    : ($diff > 0 ? ' (terlambat ' . $diff . ' hari dari due date)' : ' (tepat di due date)');
            }
        } elseif ($projectDue && $today->gt($projectDue)) {
            $statusType = 'danger';
            $statusText = 'Terlambat ' . self::days($projectDue, $today) . ' hari dari due date';
        } elseif ($projectDue) {
            $statusText = 'Berjalan, sisa ' . self::days($today, $projectDue) . ' hari ke due date';
        } else {
            $statusText = 'Berjalan (due date proyek belum diisi)';
        }

        return [
            'labels'        => $labels,
            'plan'          => $planSeries,
            'actual'        => $actualSeries,
            'start'         => $start->format('Y-m-d'),
            'due'           => $projectDue ? $projectDue->format('Y-m-d') : null,
            'today'         => $todayStr,
            'plan_today'    => $planToday,
            'actual_today'  => $actualToday,
            'deviation'     => round($actualToday - $planToday, 1),
            'status_text'   => $statusText,
            'status_type'   => $statusType,
            'has_history'   => $logs->isNotEmpty(),
            'remarks'       => $remarks,
            'divisions'     => array_map(function ($division) use ($start, $today) {
                $planDays = $division['due'] ? self::days($start, $division['due']) : 0;
                $x        = $planDays <= 0 ? 1.0 : max(0.0, min(1.0, self::days($start, $today) / $planDays));

                return [
                    'label'      => $division['label'],
                    'weight'     => $division['weight'],
                    'plan'       => $division['plan'],
                    'due'        => $division['due'] ? $division['due']->format('d/m/Y') : '-',
                    'plan_today' => round($division['plan'] * self::shape($x), 1),
                ];
            }, $divisions),
        ];
    }
}