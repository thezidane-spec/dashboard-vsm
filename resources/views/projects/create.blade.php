@extends('layouts.app')

@section('title', 'Tambah Proyek Baru - PT Mudalaya Energy Indonesia')
@section('page_title', 'TAMBAH PROYEK BARU')

@section('content')

    <div class="container-fluid" style="max-width: 1000px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark">Form Pendaftaran Proyek</h4>
                <small class="text-secondary">Input data proyek baru dan parameter dasar jadwal kerja VSM</small>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="{{ route('projects.store') }}" method="POST">
                    @csrf

                    <h6 class="fw-bold mb-3 text-success"><i class="bi bi-info-circle me-1"></i> Informasi Dasar Proyek</h6>
                    <div class="row g-3 form-2col">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Kode Proyek <span class="text-danger">*</span></label>
                            <input type="text" name="project_code" class="form-control" placeholder="Contoh: 2601.GOS-003" value="{{ old('project_code') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Proyek <span class="text-danger">*</span></label>
                            <input type="text" name="project_name" class="form-control" placeholder="Ketik nama proyek..." value="{{ old('project_name') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Customer / Klien <span class="text-danger">*</span></label>
                            <input type="text" name="customer" class="form-control" placeholder="Ketik nama customer..." value="{{ old('customer') }}" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Quantity (QTY) <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 1) }}" min="1" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Fabricator</label>
                            <input type="text" name="fabricator" class="form-control" placeholder="Workshop / Subcon..." value="{{ old('fabricator') }}">
                        </div>
                    </div>

                    <hr class="my-4 border-slate-200">

                    <h6 class="fw-bold mb-3 text-success"><i class="bi bi-calendar-range me-1"></i> Jadwal & Target VSM</h6>
                    <div class="row g-3 form-2col">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Contract Review</label>
                            <input type="date" name="contract_review" class="form-control" value="{{ old('contract_review') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Start Production</label>
                            <input type="date" name="start_production" class="form-control" value="{{ old('start_production') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Due Date (Target Selesai) <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Delivery Date</label>
                            <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Plan Progress Total</label>
                            <input type="text" class="form-control bg-light" value="Otomatis dari Contract Review sampai Due Date" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Note / Catatan Tambahan</label>
                            <input type="text" name="note" class="form-control" placeholder="Catatan spesifik proyek..." value="{{ old('note') }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('dashboard') }}" class="btn btn-light border px-4 fw-semibold">Batal</a>
                        <button type="submit" class="btn btn-success px-4 fw-semibold" style="background-color: var(--mei-green-brand); border-color: var(--mei-green-brand);">
                            <i class="bi bi-check-lg me-1"></i> Simpan Proyek Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection