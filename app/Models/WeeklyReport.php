<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyReport extends Model
{
    protected $guarded = [];

    protected $casts = ['section_times' => 'array', 'sections' => 'array'];

    // Tahap bawaan untuk laporan baru / laporan lama (urutan = urutan di laporan). Judul & jumlah bisa diubah per laporan.
    public const SECTIONS = [
        'material'    => 'Material',
        'fabrication' => 'Fabrication',
        'galvanized'  => 'Galvanized',
        'packing'     => 'Packing',
        'delivery'    => 'Delivery',
    ];

    public const MAX_PHOTOS_PER_SECTION = 8;
    public const MAX_SECTIONS = 12;

    public const LOGOS = [
        'mutiara'  => ['label' => 'PT. Mutiara Global Industry',  'file' => 'images/logo-weekly-mutiara.png'],
        'mudalaya' => ['label' => 'PT. Mudalaya Energy Indonesia', 'file' => 'images/logo-weekly-mudalaya.png'],
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function photos()
    {
        return $this->hasMany(WeeklyReportPhoto::class)->orderBy('sort')->orderBy('id');
    }

    /**
     * Daftar tahap laporan ini: [['key','title','time'], ...].
     * Laporan lama (kolom sections kosong) dibaca sebagai 5 tahap bawaan dengan waktu dari section_times.
     */
    public function sectionList(): array
    {
        if (is_array($this->sections) && count($this->sections)) {
            return array_values(array_map(fn($s) => [
                'key'   => (string) ($s['key'] ?? ''),
                'title' => (string) ($s['title'] ?? ''),
                'time'  => (string) ($s['time'] ?? ''),
            ], $this->sections));
        }

        $times = (array) ($this->section_times ?? []);
        $out = [];
        foreach (self::SECTIONS as $key => $label) {
            $out[] = ['key' => $key, 'title' => $label, 'time' => (string) ($times[$key] ?? '')];
        }

        return $out;
    }

    public function timeOf(string $section): string
    {
        foreach ($this->sectionList() as $s) {
            if ($s['key'] === $section) { return $s['time']; }
        }

        return '';
    }
}