{{--
    Blok "Problem Divisi" di halaman update progress.
    Variabel: $division (key Project::$remarkDepartments), $canInput (boleh menambah atau tidak), $project.
    Isinya ikut dikirim bersama form utama (multipart) dan otomatis masuk ke Remarks Report proyek.
--}}
@php
    $divLabel   = \App\Models\Project::$remarkDepartments[$division] ?? strtoupper($division);
    $divEntries = $project->remarkEntries($division);
    $jobdeskOpts = \App\Models\Project::remarkJobdeskOptions()[$division] ?? [];
    $fmtD = function ($d) { try { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '-'; } catch (\Throwable $e) { return '-'; } };
@endphp
<div class="mt-3 p-3 rounded-3 border bg-white div-problem-box" style="border-color:#fecaca !important; background:#fffafa !important;">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-bold small text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> PROBLEM DIVISI {{ strtoupper($divLabel) }}</span>
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ count($divEntries) }} problem tercatat</span>
    </div>

    @if($canInput)
        <div class="row g-2 report-input-row">
            <div class="col-md-6">
                <label class="form-label-custom">Problem</label>
                <input type="text" name="div_problem[{{ $division }}][text]" class="form-control-custom" maxlength="500" placeholder="Tulis masalah di divisi ini (kosongkan jika tidak ada)">
            </div>
            <div class="col-md-3">
                <label class="form-label-custom">Tanggal Problem</label>
                <input type="date" name="div_problem[{{ $division }}][date]" class="form-control-custom" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label-custom">PIC</label>
                <input type="text" name="div_problem[{{ $division }}][pic]" class="form-control-custom" maxlength="100" value="{{ auth()->user()->name }}">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Jobdesk Terkait</label>
                <div class="dropdown" data-jobdesk-dd>
                    <input type="hidden" name="div_problem[{{ $division }}][jobdesk]" value="">
                    <button type="button" class="form-control-custom text-start d-flex justify-content-between align-items-center dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                        <span class="jd-label text-muted">Pilih jobdesk...</span>
                    </button>
                    <ul class="dropdown-menu shadow border-0 w-100" style="max-height:260px; overflow-y:auto;">
                        <li><button type="button" class="dropdown-item small text-muted" data-jd-value="">- Tidak ada / umum -</button></li>
                        @foreach($jobdeskOpts as $jk => $jn)
                            <li><button type="button" class="dropdown-item small" data-jd-value="{{ $jk }}">{{ $jn }}</button></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Root Cause</label>
                <input type="text" name="div_problem[{{ $division }}][root_cause]" class="form-control-custom" maxlength="500" placeholder="Akar penyebab">
            </div>
            <div class="col-md-3">
                <label class="form-label-custom">Action</label>
                <input type="text" name="div_problem[{{ $division }}][action]" class="form-control-custom" maxlength="500" placeholder="Tindakan perbaikan">
            </div>
            <div class="col-md-3">
                <label class="form-label-custom">Due Date Action</label>
                <input type="date" name="div_problem[{{ $division }}][action_due]" class="form-control-custom">
            </div>
            <div class="col-12">
                <label class="form-label-custom">Evidence (opsional)</label>
                <input type="file" name="div_problem[{{ $division }}][evidence]" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.webp,.xlsx,.docx">
                <textarea name="div_problem[{{ $division }}][evidence_text]" rows="2" maxlength="1000" class="form-control form-control-sm mt-2" placeholder="Atau ketik evidence berupa teks / keterangan / link..."></textarea>
                <div class="form-text">Evidence bisa berupa file (PDF / gambar / Excel / Word, maks 10 MB), teks ketikan, atau keduanya. Problem baru tersimpan saat tombol simpan di halaman ini ditekan dan otomatis masuk ke Remarks Report.</div>
            </div>
        </div>
    @endif

    @if(count($divEntries))
        <div class="table-responsive mt-3">
            <table class="table table-sm table-bordered mb-0" style="font-size:0.78rem;">
                <thead class="table-light">
                    <tr><th>Problem</th><th>Jobdesk</th><th>Root Cause</th><th>Action</th><th>Due</th><th>PIC</th><th>Evidence</th></tr>
                </thead>
                <tbody>
                    @foreach($divEntries as $en)
                        <tr>
                            <td>{{ $en['problem'] ?: '-' }} <span class="badge bg-warning-subtle text-warning-emphasis">{{ $fmtD($en['date']) }}</span></td>
                            <td>@if($en['jobdesk_label'])<span class="badge bg-primary-subtle text-primary-emphasis">{{ $en['jobdesk_label'] }}</span>@else - @endif</td>
                            <td>{{ $en['root_cause'] ?: '-' }}</td>
                            <td>{{ $en['action'] ?: '-' }}</td>
                            <td>{{ $fmtD($en['due_date']) }}</td>
                            <td>{{ $en['pic'] ?: '-' }}</td>
                            <td>
                                @if($en['evidence'])
                                    <a href="{{ route('projects.evidence', ['project' => $project->id, 'path' => $en['evidence']['path']]) }}" target="_blank"><i class="bi bi-paperclip"></i> {{ \Illuminate\Support\Str::limit($en['evidence']['name'] ?? 'Evidence', 16) }}</a>
                                @endif
                                @if(($en['evidence_text'] ?? '') !== '')
                                    <div class="small" style="white-space: pre-line; min-width:140px;">{{ $en['evidence_text'] }}</div>
                                @endif
                                @if(!$en['evidence'] && ($en['evidence_text'] ?? '') === '') - @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@once
    <script>
        // Dropdown button "Jobdesk Terkait": isi hidden input dengan key jobdesk yang dipilih
        document.addEventListener('click', function (e) {
            const item = e.target.closest('[data-jd-value]');
            if (!item) { return; }
            const dd = item.closest('[data-jobdesk-dd]');
            if (!dd) { return; }
            dd.querySelector('input[type=hidden]').value = item.getAttribute('data-jd-value');
            const lbl = dd.querySelector('.jd-label');
            const v = item.getAttribute('data-jd-value');
            lbl.textContent = v ? item.textContent.trim() : 'Pilih jobdesk...';
            lbl.classList.toggle('text-muted', !v);
        });
    </script>
@endonce