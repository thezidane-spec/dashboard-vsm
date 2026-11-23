<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Support\MaterialReport;
use Illuminate\Console\Command;

class SyncMaterialProgress extends Command
{
    protected $signature = 'material:sync-progress {--dry-run : Hanya tampilkan perubahan, jangan simpan}';

    protected $description = 'Samakan persen Raw Material (task_main_material) semua proyek dengan persen barang datang';

    public function handle(): int
    {
        $dry     = (bool) $this->option('dry-run');
        $changed = 0;

        foreach (Project::orderBy('id')->get() as $project) {
            $old = (int) $project->task_main_material;

            if ($dry) {
                $new = MaterialReport::summary($project->materialItems()->with('arrivals')->get())['progress'];
            } else {
                $new = $project->syncMainMaterialProgress();
            }

            if ($new !== $old) {
                $changed++;
                $this->line(sprintf('#%d %s: %d%% -> %d%%', $project->id, $project->project_name, $old, $new));
            }
        }

        $this->info(($dry ? '[dry-run] ' : '') . $changed . ' proyek ' . ($dry ? 'akan berubah.' : 'diperbarui.'));

        return self::SUCCESS;
    }
}