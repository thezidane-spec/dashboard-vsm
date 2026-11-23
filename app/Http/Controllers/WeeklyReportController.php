<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\WeeklyReport;
use App\Models\WeeklyReportPhoto;
use App\Support\WeeklyReportXlsx;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Weekly Report (Foto Progress) per proyek.
 * Semua user login boleh melihat & mengekspor; hanya admin dan PPIC yang boleh membuat / mengubah / menghapus.
 */
class WeeklyReportController extends Controller
{
    private const EDIT_ROLES = ['admin', 'ppic'];
    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp'];
    private const MAX_FILE_BYTES = 15 * 1024 * 1024;
    private const MAX_SIDE = 1600;   // foto diperkecil agar file Excel tidak membengkak

    public function store(Request $request, Project $project)
    {
        $this->authorizeEdit();
        $report = new WeeklyReport(['project_id' => $project->id, 'created_by' => auth()->id()]);

        return $this->save($request, $project, $report, true);
    }

    public function update(Request $request, Project $project, WeeklyReport $report)
    {
        $this->authorizeEdit();
        $this->ensureBelongs($project, $report);

        return $this->save($request, $project, $report, false);
    }

    public function destroy(Project $project, WeeklyReport $report)
    {
        $this->authorizeEdit();
        $this->ensureBelongs($project, $report);

        foreach ($report->photos as $photo) {
            Storage::disk('local')->delete($photo->path);
        }
        $label = $report->period ?: ('#' . $report->id);
        $report->delete();
        $this->log($project, 'Weekly Report "' . $label . '" dihapus');

        return $this->back($project)->with('weekly_status', 'Weekly Report dihapus.');
    }

    // Tampilkan foto (hanya milik laporan proyek ini)
    public function photo(Project $project, WeeklyReport $report, WeeklyReportPhoto $photo)
    {
        $this->ensureBelongs($project, $report);
        abort_unless($photo->weekly_report_id === $report->id && Storage::disk('local')->exists($photo->path), 404);

        return response()->file(Storage::disk('local')->path($photo->path), ['Cache-Control' => 'private, max-age=3600']);
    }

    public function export(Project $project, WeeklyReport $report)
    {
        $this->ensureBelongs($project, $report);

        try {
            $path = WeeklyReportXlsx::create($report, 'local');
        } catch (\Throwable $e) {
            return $this->back($project)->with('weekly_error', 'Gagal membuat file Excel: ' . $e->getMessage());
        }

        $name = 'Weekly_Report_' . Str::slug($project->project_code ?: 'proyek', '_') . '_' . now()->format('Ymd') . '.xlsx';

        return response()->download($path, $name, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);
    }

    // ---------------------------------------------------------------------------------

    private function save(Request $request, Project $project, WeeklyReport $report, bool $isNew)
    {
        $data = $request->validate([
            'period'          => 'nullable|string|max:150',
            'customer_name'   => 'nullable|string|max:255',
            'corporate_group' => 'nullable|string|max:255',
            'address'         => 'nullable|string|max:1000',
            'postcode'        => 'nullable|string|max:255',
            'language'        => 'nullable|string|max:100',
            'logo'            => 'nullable|in:' . implode(',', array_keys(WeeklyReport::LOGOS)),
            'sections'        => 'required|array|min:1|max:' . WeeklyReport::MAX_SECTIONS,
            'sections.*.key'   => ['required', 'string', 'regex:/^[a-z0-9_]{1,20}$/'],
            'sections.*.title' => 'nullable|string|max:100',
            'sections.*.time'  => 'nullable|string|max:150',
            'photos'          => 'nullable|array',
            'captions'        => 'nullable|array',
            'captions.*'      => 'nullable|array',
            'captions.*.*'    => 'nullable|string|max:200',
            'existing_captions'   => 'nullable|array',
            'existing_captions.*' => 'nullable|string|max:200',
            'remove_photos'   => 'nullable|array',
            'remove_photos.*' => 'integer',
        ]);

        // tahap-tahap (judul, jumlah, urutan, waktu) sesuai isian popup
        $sections = [];
        foreach (array_values($data['sections']) as $i => $s) {
            $key = (string) $s['key'];
            if (isset($sections[$key])) {
                return $this->back($project)->withInput()->with('weekly_error', 'Terjadi kesalahan pada daftar tahap foto. Muat ulang halaman lalu coba lagi.');
            }
            $title = trim((string) ($s['title'] ?? ''));
            $sections[$key] = ['key' => $key, 'title' => $title !== '' ? $title : 'Foto ' . ($i + 1), 'time' => trim((string) ($s['time'] ?? ''))];
        }

        // ---- cek foto SEBELUM menyimpan apa pun ----
        $removeIds = $isNew ? [] : array_map('intval', (array) ($data['remove_photos'] ?? []));
        $existing  = $isNew ? collect() : $report->photos()->get();
        $uploads   = [];
        foreach ($sections as $key => $sec) {
            $files = array_values(array_filter((array) $request->file("photos.$key"), fn($f) => $f instanceof UploadedFile));
            $kept  = $existing->where('section', $key)->whereNotIn('id', $removeIds)->count();
            if ($kept + count($files) > WeeklyReport::MAX_PHOTOS_PER_SECTION) {
                return $this->back($project)->withInput()->with('weekly_error', 'Foto "' . $sec['title'] . '" maksimal ' . WeeklyReport::MAX_PHOTOS_PER_SECTION . ' file per laporan.');
            }
            foreach ($files as $idx => $file) {
                if (!$file->isValid()) {
                    return $this->back($project)->withInput()->with('weekly_error', 'Upload foto gagal. Coba lagi.');
                }
                if (!in_array(strtolower($file->getClientOriginalExtension()), self::ALLOWED_EXT, true)) {
                    return $this->back($project)->withInput()->with('weekly_error', 'Foto harus berformat JPG, PNG, atau WEBP (' . $file->getClientOriginalName() . ').');
                }
                if ($file->getSize() > self::MAX_FILE_BYTES) {
                    return $this->back($project)->withInput()->with('weekly_error', 'Ukuran tiap foto maksimal 15 MB (' . $file->getClientOriginalName() . ').');
                }
                if (@getimagesize($file->getRealPath()) === false) {
                    return $this->back($project)->withInput()->with('weekly_error', 'File bukan gambar yang valid: ' . $file->getClientOriginalName());
                }
                $uploads[$key][] = ['file' => $file, 'caption' => $this->clean($data['captions'][$key][$idx] ?? '')];
            }
        }

        $report->fill([
            'period'          => $this->clean($data['period'] ?? ''),
            'customer_name'   => $this->clean($data['customer_name'] ?? ''),
            'corporate_group' => $this->clean($data['corporate_group'] ?? ''),
            'address'         => $this->clean($data['address'] ?? ''),
            'postcode'        => $this->clean($data['postcode'] ?? ''),
            'language'        => $this->clean($data['language'] ?? ''),
            'logo'            => $data['logo'] ?? 'mutiara',
            'sections'        => array_values($sections),
            'section_times'   => null,
        ])->save();

        // hapus foto yang ditandai, serta foto milik tahap yang dihapus dari laporan
        foreach ($existing->filter(fn($p) => in_array($p->id, $removeIds, true) || !isset($sections[$p->section])) as $photo) {
            Storage::disk('local')->delete($photo->path);
            $photo->delete();
        }

        // keterangan foto lama
        foreach ($existing as $photo) {
            if (in_array($photo->id, $removeIds, true) || !isset($sections[$photo->section])) { continue; }
            if (array_key_exists($photo->id, (array) ($data['existing_captions'] ?? []))) {
                $photo->update(['caption' => $this->clean($data['existing_captions'][$photo->id])]);
            }
        }

        // simpan foto baru
        $sort = (int) $report->photos()->max('sort');
        foreach ($uploads as $key => $files) {
            foreach ($files as ['file' => $file, 'caption' => $caption]) {
                $stored = $this->storeImage($project, $report, $file);
                WeeklyReportPhoto::create([
                    'weekly_report_id' => $report->id,
                    'section'          => $key,
                    'path'             => $stored,
                    'original_name'    => mb_substr($file->getClientOriginalName(), 0, 150),
                    'caption'          => $caption,
                    'sort'             => ++$sort,
                ]);
            }
        }

        $label = $report->period ?: ('#' . $report->id);
        $this->log($project, 'Weekly Report "' . $label . '" ' . ($isNew ? 'dibuat' : 'diperbarui'));

        return $this->back($project)->with('weekly_status', $isNew ? 'Weekly Report dibuat.' : 'Weekly Report diperbarui.');
    }

    /**
     * Simpan foto sebagai JPEG yang diperkecil (maks. 1600 px) dan sudah dibenarkan orientasinya (EXIF).
     * Bila GD tidak tersedia, file asli (JPG/PNG) disimpan apa adanya.
     */
    private function storeImage(Project $project, WeeklyReport $report, UploadedFile $file): string
    {
        $dir  = "weekly/{$project->id}/{$report->id}";
        $name = Str::random(24);
        $ext  = strtolower($file->getClientOriginalExtension());

        if (extension_loaded('gd')) {
            $src = null;
            $real = $file->getRealPath();
            $type = @exif_imagetype($real);
            if ($type === IMAGETYPE_JPEG) { $src = @imagecreatefromjpeg($real); }
            elseif ($type === IMAGETYPE_PNG) { $src = @imagecreatefrompng($real); }
            elseif ($type === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) { $src = @imagecreatefromwebp($real); }

            if ($src) {
                if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                    $exif = @exif_read_data($real);
                    $deg = match ((int) ($exif['Orientation'] ?? 1)) { 3 => 180, 6 => -90, 8 => 90, default => 0 };
                    if ($deg !== 0 && ($rot = imagerotate($src, $deg, 0))) { $src = $rot; }
                }
                $w = imagesx($src); $h = imagesy($src);
                $scale = min(1, self::MAX_SIDE / max($w, $h));
                $nw = max(1, (int) round($w * $scale)); $nh = max(1, (int) round($h * $scale));
                $dst = imagecreatetruecolor($nw, $nh);
                imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));   // latar putih untuk PNG transparan
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                ob_start();
                imagejpeg($dst, null, 85);
                $jpeg = (string) ob_get_clean();
                imagedestroy($src); imagedestroy($dst);
                if ($jpeg !== '') {
                    $path = "$dir/$name.jpg";
                    Storage::disk('local')->put($path, $jpeg);
                    return $path;
                }
            }
        }

        $ext = in_array($ext, ['jpg', 'jpeg', 'png'], true) ? $ext : 'jpg';

        return $file->storeAs($dir, "$name.$ext", 'local');
    }

    private function clean($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function authorizeEdit(): void
    {
        $role = strtolower((string) (auth()->user()->role ?? ''));
        abort_unless(in_array($role, self::EDIT_ROLES, true), 403, 'Hanya admin dan PPIC yang dapat mengubah Weekly Report.');
    }

    private function ensureBelongs(Project $project, WeeklyReport $report): void
    {
        abort_unless($report->project_id === $project->id, 404);
    }

    private function back(Project $project)
    {
        return redirect()->to(route('projects.edit', $project->id) . '#weekly-section');
    }

    private function log(Project $project, string $text): void
    {
        try {
            ActivityLog::create([
                'project_id'  => $project->id,
                'user_id'     => auth()->id(),
                'action'      => 'WEEKLY_REPORT',
                'department'  => strtolower((string) (auth()->user()->role ?? 'ppic')),
                'ip_address'  => request()->ip(),
                'description' => $text,
            ]);
        } catch (\Throwable $e) {
            // log aktivitas bersifat pelengkap; jangan gagalkan penyimpanan
        }
    }
}