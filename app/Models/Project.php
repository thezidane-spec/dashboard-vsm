<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Project extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Auto-cast atribut tanggal ke instance Carbon
    protected $casts = [
        'due_date'             => 'date',
        'engineering_tasks_disabled' => 'array',
        'tasks_disabled'       => 'array',
        'remarks_problems'     => 'array',
        'remarks_tasks'        => 'array',
        'remarks_pics'         => 'array',
        'remarks_updated_at'   => 'datetime',
        'contract_review'      => 'date',
        'start_production'     => 'date',
        'delivery_date'        => 'date',
        'actual_delivery_date' => 'date',
        'actual_finish_date' => 'date',
        'actual_material_arrival_date' => 'date',
        'due_date_engineering' => 'date',
        'due_date_procurement'  => 'date',
        'due_date_ppic'        => 'date',
        'due_date_production'  => 'date',
        'due_date_qc'          => 'date',
        'due_date_qaqc'        => 'date',
        'due_date_preparation'      => 'date',
        'due_date_main_production'  => 'date',
        // Due Date & Tanggal Update PER JOB Divisi Engineering (12 job)
        'due_date_task_drawing_ga'    => 'date',
        'last_update_task_drawing_ga' => 'date',
        'due_date_task_detail_drawing'    => 'date',
        'last_update_task_detail_drawing' => 'date',
        'due_date_task_cutting_plan'    => 'date',
        'last_update_task_cutting_plan' => 'date',
        'due_date_task_cnc_program'    => 'date',
        'last_update_task_cnc_program' => 'date',
        'due_date_task_bom'    => 'date',
        'last_update_task_bom' => 'date',
        'due_date_task_painting_calc'    => 'date',
        'last_update_task_painting_calc' => 'date',
        'due_date_task_design_calc'    => 'date',
        'last_update_task_design_calc' => 'date',
        'due_date_task_design_name_plate'    => 'date',
        'last_update_task_design_name_plate' => 'date',
        'due_date_task_design_sticker'    => 'date',
        'last_update_task_design_sticker' => 'date',
        'due_date_task_packing_drawing'    => 'date',
        'last_update_task_packing_drawing' => 'date',
        'due_date_task_welding_map'    => 'date',
        'last_update_task_welding_map' => 'date',
        'due_date_task_skema_loadtest'    => 'date',
        'last_update_task_skema_loadtest' => 'date',
        // Due Date & Tanggal Update PER JOB Tahap Preparation (Marking, Cutting, Drilling)
        'due_date_task_marking'    => 'date',
        'last_update_task_marking' => 'date',
        'due_date_task_cutting'    => 'date',
        'last_update_task_cutting' => 'date',
        'due_date_task_drilling'    => 'date',
        'last_update_task_drilling' => 'date',
        // Due Date & Tanggal Update PER JOB Tahap Production (Fit Up, Welding, Finishing)
        'due_date_task_fit_up'    => 'date',
        'last_update_task_fit_up' => 'date',
        'due_date_task_welding'    => 'date',
        'last_update_task_welding' => 'date',
        'due_date_task_finishing'    => 'date',
        'last_update_task_finishing' => 'date',
        // Due Date & Tanggal Update PER JOB Divisi Finishing (Packing s/d BAPP)
        'due_date_task_packing'    => 'date',
        'last_update_task_packing' => 'date',
        'due_date_task_blasting'    => 'date',
        'last_update_task_blasting' => 'date',
        'due_date_task_painting'    => 'date',
        'last_update_task_painting' => 'date',
        'due_date_task_load_test'    => 'date',
        'last_update_task_load_test' => 'date',
        'due_date_task_another_test'    => 'date',
        'last_update_task_another_test' => 'date',
        'due_date_task_leak_test'    => 'date',
        'last_update_task_leak_test' => 'date',
        'due_date_task_mdr'    => 'date',
        'last_update_task_mdr' => 'date',
        'due_date_task_ndt'    => 'date',
        'last_update_task_ndt' => 'date',
        'due_date_task_bapp'    => 'date',
        'last_update_task_bapp' => 'date',
        // Due Date & Tanggal Update PER JOB Divisi PPIC (7 job)
        'due_date_task_master_schedule'    => 'date',
        'last_update_task_master_schedule' => 'date',
        'due_date_task_breakdown_schedule'    => 'date',
        'last_update_task_breakdown_schedule' => 'date',
        'due_date_task_pr'    => 'date',
        'last_update_task_pr' => 'date',
        'due_date_task_vendor'    => 'date',
        'last_update_task_vendor' => 'date',
        'due_date_task_surat_jalan'    => 'date',
        'last_update_task_surat_jalan' => 'date',
        'due_date_task_progress_report'    => 'date',
        'last_update_task_progress_report' => 'date',
        'due_date_task_berita_acara'    => 'date',
        'last_update_task_berita_acara' => 'date',
        'task_main_material_purchase_date'      => 'date',
        'task_main_material_arrival_date'       => 'date',
        'task_secondary_material_purchase_date' => 'date',
        'task_secondary_material_arrival_date'  => 'date',
        'task_consumable_purchase_date'         => 'date',
        'task_consumable_arrival_date'          => 'date',
        'task_sparepart_purchase_date'          => 'date',
        'task_sparepart_arrival_date'           => 'date',
        // Start Date PER JOB (semua jobdesk)
        'start_date_task_drawing_ga' => 'date',
        'start_date_task_detail_drawing' => 'date',
        'start_date_task_cutting_plan' => 'date',
        'start_date_task_cnc_program' => 'date',
        'start_date_task_bom' => 'date',
        'start_date_task_painting_calc' => 'date',
        'start_date_task_design_calc' => 'date',
        'start_date_task_design_name_plate' => 'date',
        'start_date_task_design_sticker' => 'date',
        'start_date_task_packing_drawing' => 'date',
        'start_date_task_welding_map' => 'date',
        'start_date_task_skema_loadtest' => 'date',
        'start_date_task_main_material' => 'date',
        'start_date_task_secondary_material' => 'date',
        'start_date_task_painting_material' => 'date',
        'start_date_task_master_schedule' => 'date',
        'start_date_task_breakdown_schedule' => 'date',
        'start_date_task_pr' => 'date',
        'start_date_task_vendor' => 'date',
        'start_date_task_surat_jalan' => 'date',
        'start_date_task_progress_report' => 'date',
        'start_date_task_berita_acara' => 'date',
        'start_date_task_bapp' => 'date',
        'start_date_task_marking' => 'date',
        'start_date_task_cutting' => 'date',
        'start_date_task_drilling' => 'date',
        'start_date_task_fit_up' => 'date',
        'start_date_task_welding' => 'date',
        'start_date_task_finishing' => 'date',
        'start_date_task_packing' => 'date',
        'start_date_task_blasting' => 'date',
        'start_date_task_painting' => 'date',
        'start_date_task_load_test' => 'date',
        'start_date_task_another_test' => 'date',
        'start_date_task_leak_test' => 'date',
        'start_date_task_mdr' => 'date',
        'start_date_task_ndt' => 'date',
        'due_date_task_secondary_material'       => 'date',
        'last_update_task_secondary_material'    => 'date',
        'due_date_task_painting_material'        => 'date',
        // Jobdesk baru (Preparation, Finish Process, QA/QC)
        'due_date_task_prep_drilling' => 'date',
        'last_update_task_prep_drilling' => 'date',
        'start_date_task_prep_drilling' => 'date',
        'due_date_task_rolling' => 'date',
        'last_update_task_rolling' => 'date',
        'start_date_task_rolling' => 'date',
        'due_date_task_bending' => 'date',
        'last_update_task_bending' => 'date',
        'start_date_task_bending' => 'date',
        'due_date_task_machining' => 'date',
        'last_update_task_machining' => 'date',
        'start_date_task_machining' => 'date',
        'due_date_task_galvanizing' => 'date',
        'last_update_task_galvanizing' => 'date',
        'start_date_task_galvanizing' => 'date',
        'due_date_task_prep_finishing' => 'date',
        'last_update_task_prep_finishing' => 'date',
        'start_date_task_prep_finishing' => 'date',
        'due_date_task_finishing_after_galv' => 'date',
        'last_update_task_finishing_after_galv' => 'date',
        'start_date_task_finishing_after_galv' => 'date',
        'due_date_task_electro_plating' => 'date',
        'last_update_task_electro_plating' => 'date',
        'start_date_task_electro_plating' => 'date',
        'due_date_task_inspection_plan' => 'date',
        'last_update_task_inspection_plan' => 'date',
        'start_date_task_inspection_plan' => 'date',
        'custom_tasks' => 'array',
        'due_date_task_material_inspection' => 'date',
        'last_update_task_material_inspection' => 'date',
        'start_date_task_material_inspection' => 'date',
        'due_date_task_qa_load_test' => 'date',
        'last_update_task_qa_load_test' => 'date',
        'start_date_task_qa_load_test' => 'date',
        'due_date_task_qa_another_test' => 'date',
        'last_update_task_qa_another_test' => 'date',
        'start_date_task_qa_another_test' => 'date',
        'due_date_task_qa_leak_test' => 'date',
        'last_update_task_qa_leak_test' => 'date',
        'start_date_task_qa_leak_test' => 'date',
        'due_date_task_welding_report' => 'date',
        'last_update_task_welding_report' => 'date',
        'start_date_task_welding_report' => 'date',
        'due_date_task_painting_report' => 'date',
        'last_update_task_painting_report' => 'date',
        'start_date_task_painting_report' => 'date',
        'due_date_task_inspection_customer' => 'date',
        'last_update_task_inspection_customer' => 'date',
        'start_date_task_inspection_customer' => 'date',
        'due_date_task_galvanized_report' => 'date',
        'last_update_task_galvanized_report' => 'date',
        'start_date_task_galvanized_report' => 'date',
        'last_update_task_painting_material'     => 'date',
        'task_painting_material_purchase_date'   => 'date',
        'task_painting_material_arrival_date'    => 'date',
    ];

    // Pilihan status pengiriman & kesesuaian barang untuk detail item Procurement
    public static array $shippingStatusOptions = [
        'menunggu_dikirim' => 'Menunggu Dikirim',
        'dalam_pengiriman' => 'Dalam Pengiriman',
        'sudah_sampai'     => 'Sudah Sampai',
    ];

    public static array $matchStatusOptions = [
        'belum_dicek'  => 'Belum Dicek',
        'sesuai'       => 'Sesuai',
        'tidak_sesuai' => 'Tidak Sesuai',
    ];

    // Weekly Report (Foto Progress): terbaru di atas
    public function weeklyReports()
    {
        return $this->hasMany(WeeklyReport::class)->latest('id');
    }

    // Relasi ke DailyReport (Urutkan dari laporan harian terbaru)
    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class)->latest('report_date')->latest('created_at');
    }

   // Relasi ke ActivityLog (Urutkan dari log aktivitas terbaru)
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }

   // Relasi ke ProjectIssue
    public function issues()
    {
        return $this->hasMany(\App\Models\ProjectIssue::class)->latest();
    }

   // Relasi ke TaskCompletion (kapan tiap task checklist ditandai selesai)
    public function taskCompletions()
    {
        return $this->hasMany(\App\Models\TaskCompletion::class);
    }

   // Relasi ke MaterialItem (daftar Raw Material & Vendor Raw Material milik Procurement)
    public function materialItems()
    {
        return $this->hasMany(\App\Models\MaterialItem::class)->orderBy('id');
    }

   // Relasi ke TaskProgress (task mana yang statusnya sedang dikerjakan)
    public function taskProgress()
    {
        return $this->hasMany(\App\Models\TaskProgress::class);
    }

    // Hitung Sisa Waktu Penyelesaian Utama (Dalam Hari)
    public function getDaysRemainingAttribute()
    {
        if (!$this->due_date) return 0;

        $today = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($this->due_date)->startOfDay();

        return (int) $today->diffInDays($dueDate, false);
    }

    // Helper Hitung Sisa Waktu Penyelesaian Spesifik Per Departemen (Dalam Hari)
    public function getDepartmentDaysRemaining($deptKey)
    {
        $dateAttr = "due_date_{$deptKey}";
        if (!$this->$dateAttr) return 0;

        $today = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($this->$dateAttr)->startOfDay();

        return (int) $today->diffInDays($dueDate, false);
    }

    // Daftar kunci jobdesk milik satu divisi / tahap (untuk mencari kapan divisi itu selesai)
    private function divisionTaskKeys(string $deptKey): array
    {
        switch ($deptKey) {
            case 'engineering': return array_keys(self::$engineeringTasks);
            case 'procurement': return array_keys(self::$procurementTasks);
            case 'ppic':        return array_keys($this->activeTasks('ppic'));
            case 'qc':          return array_keys($this->activeTasks('finishing'));
            case 'qaqc':        return array_keys($this->activeTasks('qaqc'));
        }

        $keys = [];
        foreach (self::$productionStages as $stageKey => $stage) {
            if ($deptKey === 'production' || $deptKey === ($stage['field_key'] ?? $stageKey) || $deptKey === $stageKey) {
                $keys = array_merge($keys, array_keys($this->activeTasks($stageKey)));
            }
        }

        return $keys;
    }

    // Tanggal divisi selesai = tanggal jobdesk TERAKHIR mencapai 100% (null bila tidak ada catatan, mis. data lama)
    public function divisionFinishedAt(string $deptKey): ?Carbon
    {
        $keys = $this->divisionTaskKeys($deptKey);
        if (empty($keys)) {
            return null;
        }

        $latest = $this->taskCompletions()->whereIn('task_key', $keys)->max('completed_at');

        return $latest ? Carbon::parse($latest) : null;
    }

    // Info keterangan tenggat per departemen untuk ditampilkan di halaman edit.
    // Return: ['status' => ..., 'label' => ..., 'text_class' => ..., 'icon' => ..., 'days' => int|null]
    //   - no_due_date : belum ada tanggal tenggat
    //   - done        : progress divisi sudah 100% (tidak dianggap terlambat)
    //   - late        : tenggat sudah lewat & progress belum 100%  -> "Terlambat N Hari"
    //   - due_today   : tenggat jatuh hari ini & belum selesai
    //   - upcoming    : tenggat masih ke depan -> "N Hari Tersisa"
    public function getDepartmentDeadlineInfo($deptKey)
    {
        $dateAttr     = "due_date_{$deptKey}";
        $progressAttr = "progress_{$deptKey}";

        if (!$this->$dateAttr) {
            return [
                'status'     => 'no_due_date',
                'label'      => 'Belum Ada Tenggat',
                'text_class' => 'text-secondary',
                'icon'       => 'bi-calendar-x',
                'days'       => null,
            ];
        }

        if ((int) $this->$progressAttr >= 100) {
            // Divisi sudah selesai: cek apakah selesainya melewati tenggat divisi (dari catatan task_completions)
            $finishedAt = $this->divisionFinishedAt($deptKey);
            if ($finishedAt) {
                $due  = Carbon::parse($this->$dateAttr)->startOfDay();
                $done = $finishedAt->copy()->startOfDay();
                if ($done->gt($due)) {
                    $late = (int) $due->diffInDays($done);
                    return [
                        'status'      => 'done_late',
                        'label'       => 'Selesai terlambat ' . $late . ' hari (selesai ' . $done->format('d/m/Y') . ')',
                        'text_class'  => 'text-danger',
                        'icon'        => 'bi-exclamation-triangle-fill',
                        'days'        => -$late,
                        'finished_at' => $done,
                    ];
                }
            }

            return [
                'status'     => 'done',
                'label'      => 'Selesai' . ($finishedAt ? ' (' . $finishedAt->format('d/m/Y') . ')' : ''),
                'text_class' => 'text-success',
                'icon'       => 'bi-check-circle-fill',
                'days'       => null,
            ];
        }

        $days = $this->getDepartmentDaysRemaining($deptKey);

        if ($days < 0) {
            return [
                'status'     => 'late',
                'label'      => 'Terlambat ' . abs($days) . ' Hari',
                'text_class' => 'text-danger',
                'icon'       => 'bi-exclamation-triangle-fill',
                'days'       => $days,
            ];
        }

        if ($days === 0) {
            return [
                'status'     => 'due_today',
                'label'      => 'Jatuh Tempo Hari Ini',
                'text_class' => 'text-warning',
                'icon'       => 'bi-clock-history',
                'days'       => 0,
            ];
        }

        return [
            'status'     => 'upcoming',
            'label'      => $days . ' Hari Tersisa',
            'text_class' => $days <= 3 ? 'text-warning' : 'text-success',
            'icon'       => 'bi-clock-history',
            'days'       => $days,
        ];
    }

    /**
     * Keterangan tenggat PER JOBDESK (dari due_date_<task> + progress + tanggal selesai di task_completions).
     * status: no_due_date | done | done_late | late | due_today | upcoming ; 'short' = teks ringkas untuk badge.
     */
    public function getTaskDeadlineInfo(string $taskKey): array
    {
        if ($taskKey === 'task_inspection_plan' && $this->inspectionPlanIsNa()) {
            return ['status' => 'na', 'short' => 'N/A', 'label' => 'N/A (tidak perlu dikerjakan)', 'days' => null, 'class' => 'secondary'];
        }

        $dueAttr = "due_date_{$taskKey}";
        $due     = $this->$dueAttr ? Carbon::parse($this->$dueAttr)->startOfDay() : null;
        $pct     = min(100, max(0, (int) $this->$taskKey));

        // Jobdesk tanpa Due Date sendiri memakai tenggat divisi/tahapnya (supaya jobdesk baru tetap punya keterangan)
        $inherited = false;
        if (!$due) {
            $division = self::taskDivision($taskKey) ?? (array_key_exists($taskKey, self::$procurementTasks) ? 'procurement' : null);
            $divDueAttr = ['finishing' => 'due_date_qc'][$division] ?? ($division ? "due_date_{$division}" : null);
            if ($divDueAttr && $this->$divDueAttr) {
                $due = Carbon::parse($this->$divDueAttr)->startOfDay();
                $inherited = true;
            }
        }

        if (!$due) {
            return ['status' => 'no_due_date', 'short' => 'Belum ada tenggat', 'label' => 'Belum ada tenggat', 'days' => null, 'class' => 'secondary'];
        }

        if ($pct >= 100) {
            $completion = $this->taskCompletions->firstWhere('task_key', $taskKey);
            if ($completion && $completion->completed_at) {
                $done = Carbon::parse($completion->completed_at)->startOfDay();
                if ($done->gt($due)) {
                    $late = (int) $due->diffInDays($done);
                    return ['status' => 'done_late', 'short' => 'Terlambat selesai ' . $late . ' hari', 'label' => 'Terlambat selesai ' . $late . ' hari (selesai ' . $done->format('d/m/Y') . ')', 'days' => -$late, 'class' => 'danger', 'inherited' => $inherited];
                }
            }
            return ['status' => 'done', 'short' => 'Selesai', 'label' => 'Selesai tepat waktu', 'days' => null, 'class' => 'success', 'inherited' => $inherited];
        }

        $days = (int) Carbon::today()->diffInDays($due, false);
        if ($days < 0) {
            return ['status' => 'late', 'short' => 'Terlambat ' . abs($days) . ' hari', 'label' => 'Terlambat ' . abs($days) . ' hari', 'days' => $days, 'class' => 'danger', 'inherited' => $inherited];
        }
        if ($days === 0) {
            return ['status' => 'due_today', 'short' => 'Jatuh tempo hari ini', 'label' => 'Jatuh tempo hari ini', 'days' => 0, 'class' => 'warning', 'inherited' => $inherited];
        }
        return ['status' => 'upcoming', 'short' => $days . ' hari lagi', 'label' => $days . ' hari tersisa', 'days' => $days, 'class' => $days <= 3 ? 'warning' : 'success', 'inherited' => $inherited];
    }

    /**
     * Daftar jobdesk yang terlambat (sedang telat / selesai terlambat) di sebuah divisi/tahap -- untuk ringkasan di header divisi.
     * @return array<int, array{key:string,name:string,info:array}>
     */
    public function lateTasksOf(string $division, array $info): array
    {
        $out = [];
        foreach (array_keys($this->activeTasks($division)) as $taskKey) {
            $ti = $this->getTaskDeadlineInfo($taskKey);
            if (in_array($ti['status'], ['late', 'done_late'], true)) {
                $out[] = ['key' => $taskKey, 'name' => $info[$taskKey]['name'] ?? $taskKey, 'info' => $ti];
            }
        }
        return $out;
    }

    // Bandingkan tanggal kedatangan material ACTUAL vs target (due_date_procurement)
    public function getMaterialArrivalComparisonAttribute()
    {
        if (!$this->due_date_procurement) {
            return [
                'status'      => 'no_plan',
                'label'       => 'Belum Ada Target',
                'days_diff'   => null,
                'badge_class' => 'bg-secondary-subtle text-secondary border border-secondary',
            ];
        }

        if (!$this->actual_material_arrival_date) {
            return [
                'status'      => 'pending',
                'label'       => 'Belum Datang',
                'days_diff'   => null,
                'badge_class' => 'bg-secondary-subtle text-secondary border border-secondary',
            ];
        }

        $plan   = Carbon::parse($this->due_date_procurement)->startOfDay();
        $actual = Carbon::parse($this->actual_material_arrival_date)->startOfDay();

        $daysDiff = $plan->diffInDays($actual, false);

        if ($daysDiff > 0) {
            return [
                'status'      => 'late',
                'label'       => "Telat {$daysDiff} Hari",
                'days_diff'   => $daysDiff,
                'badge_class' => 'bg-danger-subtle text-danger border border-danger',
            ];
        } elseif ($daysDiff < 0) {
            return [
                'status'      => 'early',
                'label'       => 'Lebih Cepat ' . abs($daysDiff) . ' Hari',
                'days_diff'   => $daysDiff,
                'badge_class' => 'bg-success-subtle text-success border border-success',
            ];
        } else {
            return [
                'status'      => 'on_time',
                'label'       => 'Tepat Waktu',
                'days_diff'   => 0,
                'badge_class' => 'bg-success-subtle text-success border border-success',
            ];
        }
    }
    // Return: ['status' => 'pending'|'on_time'|'early'|'late', 'label' => ..., 'days_diff' => int, 'badge_class' => ...]
    public function getDeliveryComparisonAttribute()
    {
        // Belum ada tanggal rencana sama sekali -> tidak bisa dibandingkan
        if (!$this->delivery_date) {
            return [
                'status'      => 'no_plan',
                'label'       => 'Belum Ada Rencana',
                'days_diff'   => null,
                'badge_class' => 'bg-secondary-subtle text-secondary border border-secondary',
            ];
        }

        // Rencana ada, tapi belum benar-benar dikirim -> masih pending
        if (!$this->actual_delivery_date) {
            return [
                'status'      => 'pending',
                'label'       => 'Belum Dikirim',
                'days_diff'   => null,
                'badge_class' => 'bg-secondary-subtle text-secondary border border-secondary',
            ];
        }

        $plan   = Carbon::parse($this->delivery_date)->startOfDay();
        $actual = Carbon::parse($this->actual_delivery_date)->startOfDay();

        // Positif = terlambat sekian hari dari rencana, Negatif = lebih cepat, Nol = tepat waktu
        $daysDiff = $plan->diffInDays($actual, false);

        if ($daysDiff > 0) {
            return [
                'status'      => 'late',
                'label'       => "Telat {$daysDiff} Hari",
                'days_diff'   => $daysDiff,
                'badge_class' => 'bg-danger-subtle text-danger border border-danger',
            ];
        } elseif ($daysDiff < 0) {
            return [
                'status'      => 'early',
                'label'       => 'Lebih Cepat ' . abs($daysDiff) . ' Hari',
                'days_diff'   => $daysDiff,
                'badge_class' => 'bg-success-subtle text-success border border-success',
            ];
        } else {
            return [
                'status'      => 'on_time',
                'label'       => 'Tepat Waktu',
                'days_diff'   => 0,
                'badge_class' => 'bg-success-subtle text-success border border-success',
            ];
        }
    }

    // Daftar departemen yang dicek untuk notifikasi keterlambatan
    public static array $departmentList = [
        'procurement' => 'Procurement',
        'ppic'        => 'PPIC',
        'engineering' => 'Engineering',
        'production'  => 'Production',
        'qc'          => 'Finishing',
        'qaqc'        => 'QA/QC',
    ];

    // Cek divisi mana saja di proyek ini yang sudah lewat due date tapi progressnya belum 100%
    public function getLateDepartments()
    {
        $late = [];

        foreach (self::$departmentList as $deptKey => $deptLabel) {
            $dueDateAttr = "due_date_{$deptKey}";
            $progressAttr = "progress_{$deptKey}";

            if (!$this->$dueDateAttr) continue; // belum ada due date, tidak dicek

            $daysRemaining = $this->getDepartmentDaysRemaining($deptKey);
            $progress = $this->$progressAttr;

            // Late = due date sudah lewat (hari tersisa negatif) DAN progress belum 100%
            if ($daysRemaining < 0 && $progress < 100) {
                $late[] = [
                    'department_key'   => $deptKey,
                    'department_label' => $deptLabel,
                    'days_late'        => abs($daysRemaining),
                    'progress'         => $progress,
                ];
            }
        }

        return $late;
    }

    // ==========================================
    // 1. CALCULATED PROGRESS PARSIAL PER DEPARTEMEN (%)
    // ==========================================

    // Daftar task departemen Engineering beserta bobotnya (Total Bobot Sektor: 20%)
    // Bobot direvisi mengikuti Summary Report PPIC: Preparation (25%) = Engineering 10% + Procurement 10% + PPIC 5%
    public static array $engineeringTasks = [
        'task_drawing_ga'         => 1,
        'task_detail_drawing'     => 2,
        'task_cutting_plan'       => 1,
        'task_cnc_program'        => 1,
        'task_bom'                => 1,
        'task_painting_calc'      => 0.25,
        'task_design_calc'        => 1,
        'task_design_name_plate'  => 0.25,
        'task_design_sticker'     => 0.25,
        'task_packing_drawing'    => 1,
        'task_welding_map'        => 1,
        'task_skema_loadtest'     => 0.25,
    ];

    // Label & deskripsi tiap job Engineering (dipakai tampilan edit proyek & Summary Report)
    public static array $engineeringTaskInfo = [
        'task_drawing_ga'        => ['name' => 'Drawing GA',         'desc' => 'General Arrangement Drawing approved.'],
        'task_detail_drawing'    => ['name' => 'Detail Drawing',     'desc' => 'Gambar manufaktur & fabrikasi detail.'],
        'task_cutting_plan'      => ['name' => 'Cutting Plan',       'desc' => 'Rencana pemotongan material disetujui.'],
        'task_cnc_program'       => ['name' => 'CNC Program',        'desc' => 'Program CNC untuk mesin potong/bentuk.'],
        'task_bom'               => ['name' => 'BOM',                'desc' => 'Bill of Materials terverifikasi.'],
        'task_painting_calc'     => ['name' => 'Painting Calc',      'desc' => 'Perhitungan spesifikasi pengecatan.'],
        'task_design_calc'       => ['name' => 'Design Calculation', 'desc' => 'Kalkulasi desain & kekuatan struktur.'],
        'task_design_name_plate' => ['name' => 'Design Name Plate',  'desc' => 'Desain name plate produk disetujui.'],
        'task_design_sticker'    => ['name' => 'Design Sticker',     'desc' => 'Desain sticker/label produk disetujui.'],
        'task_packing_drawing'   => ['name' => 'Packing Drawing',    'desc' => 'Gambar rencana pengepakan barang.'],
        'task_welding_map'       => ['name' => 'Welding Map',        'desc' => 'Peta jalur & urutan pengelasan.'],
        'task_skema_loadtest'    => ['name' => 'Skema Loadtest',     'desc' => 'Skema pengujian beban disetujui.'],
    ];

    // Departemen yang bisa dipilih sebagai PIC di Remarks Report (key disimpan, label ditampilkan)
    public static array $remarkDepartments = [
        'engineering' => 'Engineering',
        'procurement' => 'Procurement',
        'ppic'        => 'PPIC',
        'production'  => 'Production',
        'finishing'   => 'Finish Process',
        'qaqc'        => 'QA/QC',
    ];

    /**
     * Jobdesk yang bisa dipilih sebagai keterangan sebuah problem, per divisi Remarks.
     *
     * @return array<string, array<string, string>> [division => [task_key => nama jobdesk]]
     */
    public static function remarkJobdeskOptions(): array
    {
        $production = [];
        foreach (self::$productionStages as $stage) {
            $production += $stage['tasks'];
        }

        $groups = [
            'engineering' => [self::$engineeringTasks, self::$engineeringTaskInfo],
            'procurement' => [self::$procurementTasks, self::$procurementTaskInfo],
            'ppic'        => [self::$ppicTasks,        self::$ppicTaskInfo],
            'production'  => [$production,             self::$productionTaskInfo],
            'finishing'   => [self::$qcTasks,          self::$qcTaskInfo],
            'qaqc'        => [self::$qaqcTasks,        self::$qaqcTaskInfo],
        ];

        $out = [];
        foreach ($groups as $division => [$tasks, $info]) {
            foreach ($tasks as $taskKey => $weight) {
                $out[$division][$taskKey] = $info[$taskKey]['name'] ?? ($info[$taskKey]['label'] ?? ucwords(str_replace('_', ' ', substr($taskKey, 5))));
            }
        }

        return $out;
    }

    /** Nama jobdesk dari key-nya (null bila kosong / tidak dikenal). */
    public static function remarkJobdeskLabel(?string $key, ?string $division = null): ?string
    {
        if (!$key) {
            return null;
        }
        $all = self::remarkJobdeskOptions();
        if ($division && isset($all[$division][$key])) {
            return $all[$division][$key];
        }
        foreach ($all as $tasks) {
            if (isset($tasks[$key])) {
                return $tasks[$key];
            }
        }
        return null;
    }

    /** Validasi key jobdesk terhadap divisi (divisi kosong = boleh jobdesk mana saja). */
    public static function cleanRemarkJobdesk($key, ?string $division): ?string
    {
        $key = (string) $key;
        if ($key === '') {
            return null;
        }
        $all = self::remarkJobdeskOptions();
        if ($division) {
            return isset($all[$division][$key]) ? $key : null;
        }
        return self::remarkJobdeskLabel($key) !== null ? $key : null;
    }

    /**
     * Daftar remarks yang disejajarkan per nomor (problem k + action k + PIC k), dengan info Root Cause & Evidence.
     * $division = null -> semua; atau salah satu key $remarkDepartments -> hanya remarks divisi itu
     * (item lama tanpa field 'division' dianggap milik departemen PIC-nya).
     */
    public function remarkEntries(?string $division = null): array
    {
        $problems = array_values((array) ($this->remarks_problems ?? []));
        $tasks    = array_values((array) ($this->remarks_tasks ?? []));
        $pics     = array_values((array) ($this->remarks_pics ?? []));
        $count    = max(count($problems), count($tasks), count($pics));

        $out = [];
        for ($k = 0; $k < $count; $k++) {
            $p = (array) ($problems[$k] ?? []);
            $t = (array) ($tasks[$k] ?? []);
            $c = (array) ($pics[$k] ?? []);

            if (trim((string) ($p['text'] ?? '')) === '' && trim((string) ($t['text'] ?? '')) === '' && trim((string) ($c['name'] ?? '')) === '') {
                continue;
            }

            $div = ($p['division'] ?? null) ?: (($t['division'] ?? null) ?: (($c['division'] ?? null) ?: ($c['department'] ?? null)));
            if ($division !== null && $div !== $division) {
                continue;
            }

            $out[] = [
                'index'      => $k,
                'division'   => $div,
                'problem'    => (string) ($p['text'] ?? ''),
                'date'       => $p['date'] ?? null,
                'plan_at'    => !empty($p['date']) ? $this->planProgressOn($p['date']) : null,
                'root_cause' => (string) ($p['root_cause'] ?? ''),
                'jobdesk'       => ($p['jobdesk'] ?? null) ?: null,
                'jobdesk_label' => self::remarkJobdeskLabel($p['jobdesk'] ?? null, $div),
                'action'     => (string) ($t['text'] ?? ''),
                'due_date'   => $t['due_date'] ?? null,
                'pic'        => (string) ($c['name'] ?? ''),
                'pic_dept'   => $c['department'] ?? null,
                'evidence'   => (!empty($c['evidence']['path'])) ? (array) $c['evidence'] : null,
                'evidence_text' => (string) ($c['evidence_text'] ?? ''),
            ];
        }

        return $out;
    }

    /**
     * Tambah SATU entri remarks (problem + action + PIC) sejajar di ketiga daftar.
     * Daftar yang lebih pendek diisi item kosong dulu supaya nomor urutnya tetap sejajar.
     */
    public function appendRemarkEntry(array $problem, ?array $task, ?array $pic): void
    {
        $problems = array_values((array) ($this->remarks_problems ?? []));
        $tasks    = array_values((array) ($this->remarks_tasks ?? []));
        $pics     = array_values((array) ($this->remarks_pics ?? []));
        $n        = max(count($problems), count($tasks), count($pics));

        while (count($problems) < $n) { $problems[] = ['text' => '', 'date' => null, 'root_cause' => '', 'division' => null, 'jobdesk' => null]; }
        while (count($tasks) < $n)    { $tasks[]    = ['text' => '', 'due_date' => null, 'division' => null]; }
        while (count($pics) < $n)     { $pics[]     = ['name' => '', 'department' => '', 'division' => null, 'evidence' => null]; }

        $problems[] = $problem;
        $tasks[]    = $task ?? ['text' => '', 'due_date' => null, 'division' => $problem['division'] ?? null];
        $pics[]     = $pic ?? ['name' => '', 'department' => '', 'division' => $problem['division'] ?? null, 'evidence' => null];

        $this->remarks_problems   = $problems;
        $this->remarks_tasks      = $tasks;
        $this->remarks_pics       = $pics;
        $this->remarks_updated_at = now();
    }

    /**
     * Satu baris Remarks Report untuk proyek ini: info proyek + deviasi + daftar problem/task/PIC.
     * Dipakai di halaman edit proyek dan Summary Report - Remarks Report.
     * Deviation = actual progress - planning progress (negatif = di bawah rencana).
     */
    public function remarksRow(): array
    {
        $actual = (int) $this->actual_progress;
        $plan   = (int) $this->plan_progress;

        return [
            'id'         => $this->id,
            'code'       => $this->project_code,
            'name'       => $this->project_name,
            'customer'   => $this->customer,
            'due'        => $this->due_date ? $this->due_date->format('d/m/Y') : '-',
            'plan'       => $plan,
            'actual'     => $actual,
            'gap'        => $actual - $plan,
            'problems'   => array_map(function ($problem) {
                $problem = (array) $problem;
                // Planning total (%) pada tanggal masalah itu
                $problem['plan_at'] = !empty($problem['date']) ? $this->planProgressOn($problem['date']) : null;
                $problem['jobdesk_label'] = self::remarkJobdeskLabel($problem['jobdesk'] ?? null, $problem['division'] ?? null);

                return $problem;
            }, array_values((array) ($this->remarks_problems ?? []))),
            'tasks'      => array_values((array) ($this->remarks_tasks ?? [])),
            'pics'       => array_values((array) ($this->remarks_pics ?? [])),
            'entries'    => $this->remarkEntries(),
            'updated_at' => $this->remarks_updated_at,
        ];
    }

    /**
     * Jobdesk Engineering yang AKTIF di proyek ini beserta bobot efektifnya.
     * Jobdesk yang dimatikan lewat dropdown "Jobdesk" tidak dihitung, dan bobot divisi (10%) dibagi ulang
     * ke jobdesk yang tersisa secara PROPORSIONAL terhadap bobot dasarnya -- jadi total tetap 10%.
     * Proyek lama (kolom kosong) = semua aktif = bobot dasar, hasil hitungan sama seperti sebelumnya.
     *
     * @return array<string, float> [task_key => bobot efektif]
     */
    public function activeEngineeringTasks(): array
    {
        $disabled = (array) ($this->engineering_tasks_disabled ?? []);
        $active   = array_diff_key(self::$engineeringTasks, array_flip($disabled));

        // Pengaman: minimal satu jobdesk harus aktif
        if (empty($active)) {
            $active = self::$engineeringTasks;
        }

        $divisionWeight = array_sum(self::$engineeringTasks);
        $activeTotal    = array_sum($active);
        $scale          = $activeTotal > 0 ? $divisionWeight / $activeTotal : 1;

        return array_map(fn($weight) => $weight * $scale, $active);
    }

    // ==========================================
    // DROPDOWN "JOBDESK" untuk divisi selain Engineering (PPIC, Preparation, Production, Finish Process, QA/QC)
    // Pola sama dengan Engineering: jobdesk yang dimatikan tidak dihitung, bobot divisi/tahap dibagi ulang
    // PROPORSIONAL ke jobdesk yang tersisa (total bobot tetap). Kolom tasks_disabled kosong = semua aktif.
    // Kunci divisi: engineering, ppic, preparation, main_production, finishing, qaqc
    // ==========================================

    /** Bobot dasar jobdesk tiap divisi/tahap yang punya dropdown. */
    public static function taskSets(): array
    {
        return [
            'engineering'     => self::$engineeringTasks,
            'ppic'            => self::$ppicTasks,
            'preparation'     => self::$productionStages['preparation']['tasks'],
            'main_production' => self::$productionStages['main_production']['tasks'],
            'finishing'       => self::$qcTasks,
            'qaqc'            => self::$qaqcTasks,
        ];
    }

    /** Divisi pemilik sebuah jobdesk (null bila tidak punya dropdown, mis. Procurement). */
    public static function taskDivision(string $taskKey): ?string
    {
        if (str_starts_with($taskKey, 'task_custom_')) {
            return 'qaqc';
        }
        foreach (self::taskSets() as $division => $tasks) {
            if (array_key_exists($taskKey, $tasks)) {
                return $division;
            }
        }
        return null;
    }

    /** Bobot dasar jobdesk sebuah divisi/tahap untuk proyek ini (QA/QC ikut menghitung jobdesk custom proyek ini). */
    public function divisionBase(string $division): array
    {
        $base = $division === 'procurement' ? self::$procurementTasks : (self::taskSets()[$division] ?? []);
        if ($division === 'qaqc') {
            foreach ($this->customTaskList() as $row) {
                $base['task_custom_' . $row['id']] = self::CUSTOM_BASE_WEIGHT;
            }
        }

        return $base;
    }

    /** Total bobot divisi/tahap (QA/QC selalu 6% walau ada jobdesk custom). */
    public function divisionTotalWeight(string $division): float
    {
        return $division === 'qaqc' ? self::QAQC_TOTAL : (float) array_sum($this->divisionBase($division));
    }

    /** Daftar jobdesk custom QA/QC proyek ini: [['id','name','desc','progress','start_date','due_date','last_update'], ...] */
    public function customTaskList(): array
    {
        $rows = [];
        foreach ((array) ($this->custom_tasks ?? []) as $row) {
            $row = (array) $row;
            if (!empty($row['id']) && trim((string) ($row['name'] ?? '')) !== '') {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    /** Info nama + deskripsi jobdesk custom, format sama dengan $qaqcTaskInfo (key = task_custom_<id>). */
    public function customTaskInfo(): array
    {
        $info = [];
        foreach ($this->customTaskList() as $row) {
            $info['task_custom_' . $row['id']] = ['name' => (string) $row['name'], 'desc' => (string) ($row['desc'] ?? ''), 'custom' => true];
        }
        return $info;
    }

    /** Info jobdesk QA/QC lengkap: bawaan + custom. */
    public function qaqcInfoWithCustom(): array
    {
        return self::$qaqcTaskInfo + $this->customTaskInfo();
    }

    public function inspectionPlanStatus(): string
    {
        $status = (string) ($this->attributes['status_task_inspection_plan'] ?? 'open');
        return array_key_exists($status, self::$inspectionStatusOptions) ? $status : 'open';
    }

    public function inspectionPlanIsNa(): bool
    {
        return $this->inspectionPlanStatus() === 'na';
    }

    // Jobdesk custom disimpan di JSON (custom_tasks); atributnya dibaca seperti kolom biasa:
    // task_custom_<id> (persen), due_date_/last_update_/start_date_task_custom_<id> (tanggal)
    public function getAttribute($key)
    {
        if (is_string($key) && str_contains($key, 'task_custom_') && preg_match('/^(due_date_|last_update_|start_date_)?task_custom_([a-z0-9]+)$/', $key, $m)) {
            foreach ($this->customTaskList() as $row) {
                if ($row['id'] !== $m[2]) {
                    continue;
                }
                if ($m[1] === '') {
                    return (int) ($row['progress'] ?? 0);
                }
                $field = ['due_date_' => 'due_date', 'last_update_' => 'last_update', 'start_date_' => 'start_date'][$m[1]];
                return !empty($row[$field]) ? Carbon::parse($row[$field]) : null;
            }
            return $m[1] === '' ? 0 : null;
        }

        return parent::getAttribute($key);
    }

    /** @return array<string, float> [task_key => bobot efektif] jobdesk AKTIF suatu divisi/tahap */
    public function activeTasks(string $division): array
    {
        if ($division === 'engineering') {
            return $this->activeEngineeringTasks();
        }

        $base = $this->divisionBase($division);
        if (empty($base)) {
            return [];
        }

        $disabled = (array) (((array) ($this->tasks_disabled ?? []))[$division] ?? []);
        $active   = array_diff_key($base, array_flip($disabled));

        // Inspection Test Plan berstatus N/A (tidak perlu dikerjakan) tidak dihitung, sama seperti jobdesk yang tidak dicentang
        if ($division === 'qaqc' && $this->inspectionPlanIsNa()) {
            unset($active['task_inspection_plan']);
        }

        // Pengaman: minimal satu jobdesk harus aktif
        if (empty($active)) {
            $active = $base;
        }

        $total = $division === 'qaqc' ? self::QAQC_TOTAL : array_sum($base);
        $scale = $total / array_sum($active);

        return array_map(fn($weight) => $weight * $scale, $active);
    }

    public function isTaskEnabled(string $taskKey): bool
    {
        $division = self::taskDivision($taskKey);

        return $division === null || array_key_exists($taskKey, $this->activeTasks($division));
    }

    // Bobot (poin) yang sudah dicapai di divisi/tahap = jumlah (bobot efektif x persen jobdesk)
    public function divisionAchieved(string $division): float
    {
        $achieved = 0;
        foreach ($this->activeTasks($division) as $taskKey => $weight) {
            $achieved += $weight * (min(100, max(0, (int) $this->$taskKey)) / 100);
        }

        return $achieved;
    }

    public function isEngineeringTaskEnabled(string $taskKey): bool
    {
        return array_key_exists($taskKey, $this->activeEngineeringTasks());
    }

    // Progress Divisi Engineering (Total Bobot Sektor: 10%)
    public function getProgressEngineeringAttribute()
    {
        $weight = 0;
        foreach ($this->activeEngineeringTasks() as $taskKey => $taskWeight) {
            $weight += $taskWeight * (min(100, max(0, (int) $this->$taskKey)) / 100);
        }
        return round(($weight / array_sum(self::$engineeringTasks)) * 100);
    }

    // Daftar task departemen Procurement beserta bobotnya (Total Bobot Sektor: 10%)
    public static array $procurementTasks = [
        'task_main_material'      => 4,
        'task_secondary_material' => 3,
        'task_painting_material'  => 3,
    ];

    // Label & deskripsi tiap job Procurement (dipakai tampilan edit proyek & Summary Report)
    public static array $procurementTaskInfo = [
        'task_main_material'      => ['name' => 'Raw Material',       'desc' => 'Pengadaan & kedatangan material utama.'],
        'task_secondary_material' => ['name' => 'Accessories',        'desc' => 'Material pendukung, baut & aksesoris.'],
        'task_painting_material'  => ['name' => 'Painting',           'desc' => 'Pengadaan cat & material pengecatan.'],
    ];

    // Progress Divisi Procurement (Total Bobot Sektor: 10%)
    public function getProgressProcurementAttribute()
    {
        $weight = 0;
        foreach (self::$procurementTasks as $taskKey => $taskWeight) {
            $weight += $taskWeight * (min(100, max(0, (int) $this->$taskKey)) / 100);
        }
        return round(($weight / array_sum(self::$procurementTasks)) * 100);
    }

    // Daftar task departemen PPIC beserta bobotnya (Total Bobot Sektor: 7% = 5% + BAPP 2% yang dipindah dari Finishing)
    // Preparation (27%) = Engineering 10% + Procurement 10% + PPIC 7%
    public static array $ppicTasks = [
        'task_master_schedule'    => 1,
        'task_breakdown_schedule' => 0.7,
        'task_pr'                 => 0.7,
        'task_vendor'             => 0.7,
        'task_surat_jalan'        => 0.7,
        'task_progress_report'    => 0.7,
        'task_berita_acara'       => 0.5,
        'task_bapp'               => 2,
    ];

    // Label & deskripsi tiap job PPIC (dipakai tampilan edit proyek & Summary Report)
    public static array $ppicTaskInfo = [
        'task_master_schedule'    => ['name' => 'Master Schedule',        'desc' => 'Penyusunan jadwal induk proyek.'],
        'task_breakdown_schedule' => ['name' => 'Break Schedule',         'desc' => 'Rincian jadwal kerja harian/mingguan.'],
        'task_pr'                 => ['name' => 'PR Raw Material',        'desc' => 'Purchase Requisition untuk raw material.'],
        'task_vendor'             => ['name' => 'PR Purchase Part',       'desc' => 'Purchase Requisition untuk purchase part.'],
        'task_surat_jalan'        => ['name' => 'Surat Jalan',            'desc' => 'Dokumen pengiriman barang/material.'],
        'task_progress_report'    => ['name' => 'PR Painting',            'desc' => 'Purchase Requisition untuk material painting.'],
        'task_berita_acara'       => ['name' => 'Berita Acara',           'desc' => 'Berita acara serah terima/pemeriksaan.'],
        'task_bapp'               => ['name' => 'BAPP',                   'desc' => 'Berita Acara Pemeriksaan Pekerjaan.'],
    ];

    /**
     * Raw Material TIDAK diisi manual: persennya otomatis = persen barang datang (sama dengan badge
     * "x item · y% datang" di Detail Material). Nilai ini disimpan di kolom task_main_material, jadi
     * rata-rata progress Procurement, Dashboard, dan Summary Report otomatis ikut.
     * Dipanggil setiap data item / kedatangan material berubah. Mengembalikan progress terbaru.
     */
    public function syncMainMaterialProgress(?int $userId = null): int
    {
        $key = 'task_main_material';
        $new = \App\Support\MaterialReport::summary($this->materialItems()->with('arrivals')->get())['progress'];
        $old = (int) $this->$key;

        if ($new === $old) {
            return $new;
        }

        $this->forceFill([$key => $new])->save();

        // Riwayat harian (bahan S-Curve) -- sama seperti perubahan progress lewat form edit
        TaskDailyProgress::logToday($this->id, $key, $new, $userId);

        // Catat / hapus tanggal selesai & status "sedang dikerjakan" -- sama seperti DashboardController@update
        if ($new === 100 && $old !== 100) {
            TaskCompletion::updateOrCreate(
                ['project_id' => $this->id, 'task_key' => $key],
                ['user_id' => $userId, 'department' => 'procurement', 'completed_at' => now()]
            );
        }
        if ($old === 100 && $new !== 100) {
            TaskCompletion::where('project_id', $this->id)->where('task_key', $key)->delete();
        }

        $wasInProgress = $old > 0 && $old < 100;
        $isInProgress  = $new > 0 && $new < 100;

        if ($isInProgress && !$wasInProgress) {
            TaskProgress::updateOrCreate(
                ['project_id' => $this->id, 'task_key' => $key],
                ['started_by' => $userId, 'department' => 'procurement', 'started_at' => now()]
            );
        }
        if (!$isInProgress && $wasInProgress) {
            TaskProgress::where('project_id', $this->id)->where('task_key', $key)->delete();
        }

        return $new;
    }

    // Progress Divisi PPIC (Total Bobot Sektor: 7%)
    public function getProgressPpicAttribute()
    {
        return round(($this->divisionAchieved('ppic') / array_sum(self::$ppicTasks)) * 100);
    }

    // ==========================================
    // DIVISI PRODUCTION: 2 TAHAP (Total Bobot Sektor: 30%)
    // ==========================================
    //
    // Tahap Preparation (bobot 15%) : Marking, Cutting
    // Tahap Production   (bobot 35%) : Drilling, Fit Up, Welding, Finishing
    //
    // Tiap TAHAP diperlakukan seperti "mini divisi", persis pola Divisi Engineering/Procurement/dll:
    //   - due_date_<field_key> dan plan_<field_key>  -> diisi MANUAL oleh user (kolom asli di tabel projects)
    //   - progress_<field_key>                       -> DIHITUNG OTOMATIS dari checklist job di dalamnya
    //
    // Job di dalam tiap tahap (Marking, Cutting, dst) TETAP checklist boolean seperti checklist
    // divisi lain (status: not_started / in_progress / done), BUKAN input due date/plan/actual sendiri.
    // Bobot Marking sengaja dibuat paling kecil (durasi pengerjaannya biasanya paling singkat).
    // Total keenam bobot job HARUS selalu = 30.
    // Bobot direvisi mengikuti Summary Report PPIC: Production (50%) = Tahap Preparation 15% + Tahap Production 35% (Drilling dipindah ke Tahap Production)
    public static array $productionStages = [
        'preparation' => [
            'label'     => 'Tahap Preparation',
            'icon'      => 'bi-pencil-square',
            'field_key' => 'preparation', // -> due_date_preparation, plan_preparation, progress_preparation
            'tasks'     => [
                'task_marking'       => 2,
                'task_cutting'       => 2.5,
                'task_prep_drilling' => 2.5,
                'task_rolling'       => 2.5,
                'task_bending'       => 2,
                'task_machining'     => 2,
                'task_prep_finishing' => 1.5,
            ],
        ],
        'main_production' => [
            'label'     => 'Tahap Production',
            'icon'      => 'bi-gear-wide-connected',
            'field_key' => 'main_production', // -> due_date_main_production, plan_main_production, progress_main_production
            'tasks'     => [
                'task_drilling'  => 10,
                'task_fit_up'    => 10,
                'task_welding'   => 8,
                'task_finishing' => 7,
            ],
        ],
    ];

    // Label & deskripsi singkat tiap job checklist (dipakai di tampilan edit proyek)
    public static array $productionTaskInfo = [
        'task_marking'   => ['name' => 'Marking',   'desc' => 'Penandaan material sesuai gambar kerja.'],
        'task_cutting'   => ['name' => 'Cutting',   'desc' => 'Pemotongan material sesuai cutting plan.'],
        'task_drilling'  => ['name' => 'Drilling',  'desc' => 'Pelubangan material sesuai gambar kerja.'],
        'task_prep_drilling' => ['name' => 'Drilling', 'desc' => 'Pelubangan material tahap Preparation.'],
        'task_rolling'   => ['name' => 'Rolling',   'desc' => 'Pengerolan plat/pipa sesuai gambar kerja.'],
        'task_bending'   => ['name' => 'Bending',   'desc' => 'Penekukan material sesuai gambar kerja.'],
        'task_machining' => ['name' => 'Machining', 'desc' => 'Pemesinan (bubut/frais) komponen.'],
        'task_prep_finishing' => ['name' => 'Finishing', 'desc' => 'Perapian & penyelesaian akhir tahap Preparation.'],
        'task_fit_up'    => ['name' => 'Fit Up',    'desc' => 'Penyetelan & perakitan sebelum pengelasan.'],
        'task_welding'   => ['name' => 'Welding',   'desc' => 'Proses pengelasan sesuai welding map.'],
        'task_finishing' => ['name' => 'Finishing', 'desc' => 'Perapian & penyelesaian akhir hasil produksi.'],
    ];

    // Progress satu TAHAP (Preparation / Production) = persentase bobot job checklist yang sudah "done"
    public function getProductionStageProgress(string $stageKey): int
    {
        if (!isset(self::$productionStages[$stageKey])) {
            return 0;
        }

        $stageWeight = array_sum(self::$productionStages[$stageKey]['tasks']);
        if ($stageWeight <= 0) {
            return 0;
        }

        return (int) round(($this->divisionAchieved($stageKey) / $stageWeight) * 100);
    }

    // Accessor supaya $project->progress_preparation otomatis terisi (dipakai getDepartmentDeadlineInfo('preparation'))
    public function getProgressPreparationAttribute()
    {
        return $this->getProductionStageProgress('preparation');
    }

    // Accessor supaya $project->progress_main_production otomatis terisi (dipakai getDepartmentDeadlineInfo('main_production'))
    public function getProgressMainProductionAttribute()
    {
        return $this->getProductionStageProgress('main_production');
    }

    // Progress Divisi Production (Total Bobot Sektor: 50%) = total bobot job checklist "done" dari 6 job / 50
    public function getProgressProductionAttribute()
    {
        $achieved = $this->divisionAchieved('preparation') + $this->divisionAchieved('main_production');
        $total    = array_sum(self::$productionStages['preparation']['tasks']) + array_sum(self::$productionStages['main_production']['tasks']);

        return (int) round(($achieved / $total) * 100);
    }

    // Daftar task departemen Finishing (dulu QC/QA) beserta bobotnya (Total Bobot Sektor: 25%)
    // Divisi ini sekarang bernama "Finish Process" (key internal tetap 'qc'). Packing selalu paling kanan.
    public static array $qcTasks = [
        'task_blasting'      => 2.5,
        'task_painting'      => 2.5,
        'task_galvanizing'   => 2.5,
        'task_finishing_after_galv' => 1.5,
        'task_electro_plating' => 1.5,
        'task_load_test'     => 1.5,
        'task_leak_test'     => 2.5,
        'task_packing'       => 2.5,
    ];

    // Daftar task divisi QA/QC (dipisah dari Finishing) beserta bobotnya (Total Bobot Sektor: 6%)
    // Finishing 17% + QA/QC 6% + BAPP 2% (kini di PPIC) = 25% (sama seperti Finishing sebelumnya, jadi Actual Progress proyek tidak berubah)
    public static array $qaqcTasks = [
        'task_material_inspection' => 0.6,
        'task_qa_load_test'        => 0.6,
        'task_qa_another_test'     => 0.6,
        'task_qa_leak_test'        => 0.6,
        'task_mdr'                 => 0.6,
        'task_ndt'                 => 0.6,
        'task_welding_report'      => 0.6,
        'task_painting_report'     => 0.6,
        'task_galvanized_report'   => 0.6,
        'task_inspection_plan'     => 0.6,
        'task_inspection_customer' => 0.6,
    ];

    // Total bobot divisi QA/QC TETAP 6% berapa pun jumlah jobdesk custom yang ditambahkan; tiap jobdesk custom berbobot dasar sama.
    public const QAQC_TOTAL = 6.0;
    public const CUSTOM_BASE_WEIGHT = 0.6;

    // Pilihan status jobdesk Inspection Test Plan (tanpa input persen)
    public static array $inspectionStatusOptions = [
        'open'    => 'Open',
        'waiting' => 'Waiting for Customer Approval',
        'close'   => 'Close',
        'na'      => 'N/A',
    ];

    public static array $qaqcTaskInfo = [
        'task_material_inspection' => ['name' => 'Material Incoming Check',  'desc' => 'Pemeriksaan material masuk.'],
        'task_qa_load_test'        => ['name' => 'Material Dimension Check', 'desc' => 'Pemeriksaan dimensi material.'],
        'task_qa_another_test'     => ['name' => 'Load Test',                'desc' => 'Pengujian beban oleh QA/QC.'],
        'task_qa_leak_test'        => ['name' => 'Leak Test',                'desc' => 'Uji kebocoran oleh QA/QC.'],
        'task_mdr' => ['name' => 'MDR', 'desc' => 'Manufacturing Data Report disetujui.'],
        'task_ndt' => ['name' => 'NDT', 'desc' => 'Non-Destructive Testing disetujui.'],
        'task_welding_report'    => ['name' => 'Welding Check',     'desc' => 'Pemeriksaan pengelasan (welding check) disetujui.'],
        'task_painting_report'   => ['name' => 'Painting Check',    'desc' => 'Pemeriksaan pengecatan (painting check) disetujui.'],
        'task_galvanized_report' => ['name' => 'Galvanized Report', 'desc' => 'Laporan galvanis (galvanized report) disetujui.'],
        'task_inspection_customer' => ['name' => 'Inspections by Customer', 'desc' => 'Inspeksi oleh customer (inspections by customer) selesai.'],
        'task_inspection_plan'   => ['name' => 'Inspection Test Plan', 'desc' => 'Rencana inspeksi (Inspection Test Plan). Status: Open / Waiting for Customer Approval / Close / N/A.'],
    ];

    // Label & deskripsi tiap job Finishing / dulu QC-QA (dipakai tampilan edit proyek & Summary Report)
    public static array $qcTaskInfo = [
        'task_blasting'     => ['name' => 'Blasting',      'desc' => 'Sandblasting permukaan sebelum cat.'],
        'task_painting'     => ['name' => 'Painting',      'desc' => 'Pengecatan sesuai spesifikasi.'],
        'task_galvanizing'  => ['name' => 'Galvanizing',   'desc' => 'Pelapisan seng (hot dip galvanizing).'],
        'task_finishing_after_galv' => ['name' => 'Finishing After Galvanized', 'desc' => 'Perapian & penyelesaian akhir setelah galvanizing.'],
        'task_electro_plating' => ['name' => 'Electro Plating', 'desc' => 'Pelapisan logam secara elektrolisis.'],
        'task_load_test'    => ['name' => 'Load Test Preparation',    'desc' => 'Persiapan pengujian kekuatan beban mekanikal.'],
        'task_leak_test'    => ['name' => 'Leak Test Preparation',    'desc' => 'Persiapan uji kebocoran.'],
        'task_packing'      => ['name' => 'Packing',       'desc' => 'Pengepakan barang jadi.'],
    ];

    // Progress Divisi Finishing (Total Bobot Sektor: 17%, tanpa MDR, NDT & BAPP)
    public function getProgressQcAttribute()
    {
        return round(($this->divisionAchieved('finishing') / array_sum(self::$qcTasks)) * 100);
    }

    // Progress Divisi QA/QC (Total Bobot Sektor: 6%) = MDR + NDT
    public function getProgressQaqcAttribute()
    {
        return round(($this->divisionAchieved('qaqc') / self::QAQC_TOTAL) * 100);
    }

    // ==========================================
    // 1b. TIME CONSUMPTION (dipakai Summary Report PPIC & rencana fitur S-Curve)
    // ==========================================
    //
    // Time Consumption (Day) : jumlah hari kalender yang sudah berlalu sejak Contract Review
    //                          sampai hari ini (termasuk hari Minggu).
    // Time Consumption (Hour): jumlah hari yang sudah berlalu sejak Contract Review DIKURANGI
    //                          hari Minggu, dikali durasi jam kerja per hari (8 jam 30 menit = 8.5 jam).
    // Keduanya null kalau Contract Review belum diisi, dan tidak pernah negatif
    // (kalau Contract Review di masa depan, dianggap 0 hari berlalu).

    public function getTimeConsumptionDaysAttribute(): ?int
    {
        if (!$this->contract_review) {
            return null;
        }

        $start = Carbon::parse($this->contract_review)->startOfDay();
        $today = Carbon::now()->startOfDay();

        if ($today->lt($start)) {
            return 0;
        }

        return (int) $start->diffInDays($today);
    }

    public function getTimeConsumptionHoursAttribute(): ?float
    {
        $days = $this->time_consumption_days;
        if ($days === null) {
            return null;
        }
        if ($days === 0) {
            return 0.0;
        }

        $start = Carbon::parse($this->contract_review)->startOfDay();
        $workingDays = 0;

        for ($i = 1; $i <= $days; $i++) {
            if (!$start->copy()->addDays($i)->isSunday()) {
                $workingDays++;
            }
        }

        return round($workingDays * 8.5, 2);
    }

    // ==========================================
    // 2. TOTAL ACTUAL PROGRESS & STATUS GLOBAL
    // ==========================================

    // Hitung Total Actual Progress (%) Berdasarkan Bobot Keseluruhan Proyek
    public function getActualProgressAttribute()
    {
        // Engineering (Total 10%) -- hanya jobdesk yang aktif, bobotnya sudah dibagi ulang
        $engineeringWeight = 0;
        foreach ($this->activeEngineeringTasks() as $taskKey => $taskWeight) {
            $engineeringWeight += $taskWeight * (min(100, max(0, (int) $this->$taskKey)) / 100);
        }

        // Production (Total 50%) = Preparation 15% + Production 35% -- hanya jobdesk aktif
        $productionWeight = $this->divisionAchieved('preparation') + $this->divisionAchieved('main_production');

        // PPIC (Total 7%)
        $ppicWeight = $this->divisionAchieved('ppic');

        // Finish Process (Total 17%)
        $qcWeight = $this->divisionAchieved('finishing');

        // QA/QC (Total 6%)
        $qaqcWeight = $this->divisionAchieved('qaqc');

        // Procurement (Total 10%)
        $procurementWeight = 0;
        foreach (self::$procurementTasks as $taskKey => $taskWeight) {
            $procurementWeight += $taskWeight * (min(100, max(0, (int) $this->$taskKey)) / 100);
        }

        $totalWeight = 
            ($engineeringWeight * 0.01) +

            // Procurement (Total 10%)
            ($procurementWeight * 0.01) +

            // PPIC (Total 5%)
            ($ppicWeight * 0.01) +

            // Production (Total 50%)
            ($productionWeight * 0.01) +

            // Finishing / dulu QC-QA (Total 25%)
            ($qcWeight * 0.01) +

            // QA/QC (Total 6%)
            ($qaqcWeight * 0.01);

        return round($totalWeight * 100);
    }

    /**
     * PLANNING TOTAL otomatis: persen jarak hari dari Contract Review sampai Due Date proyek.
     * Contoh: Contract Review -> Due Date = 42 hari, di hari ke-5 = 12%. Bertambah tiap hari sampai 100% di Due Date.
     * Kalau Contract Review / Due Date belum diisi, dipakai nilai lama yang tersimpan di database.
     */
    public function getPlanProgressAttribute($value)
    {
        if ($this->contract_review && $this->due_date) {
            return $this->planProgressOn(\App\Support\SCurve::today());
        }

        return (int) $value;
    }

    // Planning total (%) pada tanggal tertentu (dipakai juga oleh Remarks Problem & S-Curve)
    public function planProgressOn($date): int
    {
        if (!$this->contract_review || !$this->due_date) {
            return (int) ($this->attributes['plan_progress'] ?? 0);
        }

        $start = Carbon::parse($this->contract_review)->startOfDay();
        $due   = Carbon::parse($this->due_date)->startOfDay();
        $day   = Carbon::parse($date)->startOfDay();

        $total = (int) round(($due->getTimestamp() - $start->getTimestamp()) / 86400);
        if ($total <= 0) {
            return $day->gte($due) ? 100 : 0;
        }

        $elapsed = (int) round(($day->getTimestamp() - $start->getTimestamp()) / 86400);

        return (int) max(0, min(100, round($elapsed / $total * 100)));
    }

    // Hitung Selisih Progress Global (Gap)
    public function getGapAttribute()
    {
        return $this->actual_progress - $this->plan_progress;
    }

    /**
     * Status proyek:
     *   CLOSE            (hijau)  -> sudah ada Tanggal Aktual Selesai & selesai tepat waktu (<= due date)
     *   CLOSE TERLAMBAT  (jingga) -> sudah ada Tanggal Aktual Selesai tapi LEWAT due date
     *   ON TRACK         (hijau)  -> progress 100% (belum diberi Tanggal Aktual Selesai) / divisi yang sudah 100%
     *   DELAYED          (merah)  -> belum selesai & sudah lewat due date, ATAU Actual tertinggal 5% atau lebih dari Planning
     *   AT RISK          (kuning) -> Actual tertinggal 1 - 4% dari Planning
     *   LEADING          (biru)   -> Actual sama dengan / di atas Planning
     * Dipakai juga oleh Summary Report (proyek & tiap divisi, dengan due date divisinya).
     */
    // Selisih (Planning - Actual) dalam persen: >= 5 DELAYED, 1 - 4 AT RISK
    public const DELAYED_GAP_THRESHOLD = 5;

    public static function resolveStatus(int $actual, $dueDate = null, ?int $plan = null, $finishedAt = null): array
    {
        $make = fn(string $code, string $label, string $class, string $hex) => [
            'code' => $code, 'label' => $label, 'color_class' => $class, 'class' => $class, 'bg_hex' => $hex,
        ];

        // Proyek sudah ditutup (ada Tanggal Aktual Selesai)
        if ($finishedAt) {
            $fin = Carbon::parse($finishedAt)->startOfDay();
            if ($dueDate && $fin->gt(Carbon::parse($dueDate)->startOfDay())) {
                return $make('CLOSED_LATE', 'CLOSE TERLAMBAT', 'status-badge-closed-late', '#ea580c');
            }
            return $make('ON_TRACK', 'CLOSE', 'status-badge-on-track', '#059669');
        }

        if ($actual >= 100) {
            return $make('ON_TRACK', 'ON TRACK', 'status-badge-on-track', '#059669');
        }

        if (!$dueDate) {
            return $make('NO_DUE_DATE', 'Belum Ada Tenggat', 'bg-secondary-subtle text-secondary border border-secondary', '#64748b');
        }

        $due  = Carbon::parse($dueDate)->startOfDay();
        $days = (int) round(($due->getTimestamp() - \App\Support\SCurve::today()->getTimestamp()) / 86400);

        if ($days < 0) {
            return $make('DELAYED', 'DELAYED', 'status-badge-delayed', '#dc2626');
        }

        $gap = $plan !== null ? ($plan - $actual) : 0;
        if ($gap >= self::DELAYED_GAP_THRESHOLD) {
            return $make('DELAYED', 'DELAYED', 'status-badge-delayed', '#dc2626');
        }
        if ($gap >= 1) {
            return $make('AT_RISK', 'AT RISK', 'status-badge-at-risk', '#d97706');
        }

        return $make('LEADING', 'LEADING', 'status-badge-leading', '#1e3a8a');
    }

    /**
     * Jobdesk AKTIF yang belum 100% (dipakai untuk menolak penutupan proyek).
     * @return array<int, array{division:string,name:string,progress:int}>
     */
    public function unfinishedJobdesks(): array
    {
        $groups = [
            'Engineering'    => ['engineering',     self::$engineeringTaskInfo],
            'Procurement'    => ['procurement',     self::$procurementTaskInfo],
            'PPIC'           => ['ppic',            self::$ppicTaskInfo],
            'Preparation'    => ['preparation',     self::$productionTaskInfo],
            'Production'     => ['main_production', self::$productionTaskInfo],
            'Finish Process' => ['finishing',       self::$qcTaskInfo],
            'QA/QC'          => ['qaqc',            $this->qaqcInfoWithCustom()],
        ];

        $out = [];
        foreach ($groups as $label => [$division, $info]) {
            $keys = $division === 'procurement' ? array_keys(self::$procurementTasks) : array_keys($this->activeTasks($division));
            foreach ($keys as $taskKey) {
                $pct = min(100, max(0, (int) $this->$taskKey));
                if ($pct < 100) {
                    $out[] = ['division' => $label, 'name' => $info[$taskKey]['name'] ?? $taskKey, 'progress' => $pct];
                }
            }
        }

        return $out;
    }

    // Status proyek (lihat resolveStatus)
    public function getStatusAttribute()
    {
        return self::resolveStatus((int) $this->actual_progress, $this->due_date, (int) $this->plan_progress, $this->actual_finish_date);
    }
}