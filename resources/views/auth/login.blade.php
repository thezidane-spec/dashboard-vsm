<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Visual Management Dashboard - PT Mudalaya Energy Indonesia</title>
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --mei-green-brand: #059669;
            --mei-green-dark: #047857;
            --mei-green-light: #ecfdf5;
            --mei-text-dark: #0f172a;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 440px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            background-color: var(--mei-green-light);
            border-bottom: 2px solid #a7f3d0;
            padding: 32px 24px 24px;
            text-align: center;
        }

        .brand-icon {
            width: 56px;
            height: 56px;
            background-color: var(--mei-green-brand);
            color: #ffffff;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.3);
            margin-bottom: 12px;
        }

        /* Logo + nama PT bergantian (Mudalaya <-> Mutiara) */
        .login-brand-rotator { position: relative; width: 100%; height: 118px; }
        .login-brand-slide {
            position: absolute; inset: 0;
            display: flex; flex-direction: column; align-items: center; justify-content: flex-start;
            opacity: 0;
            animation: login-brand-swap 8s ease-in-out infinite;
        }
        .login-brand-slide-b { animation-delay: 4s; }
        .login-brand-slide img { width: 64px; height: 64px; object-fit: contain; margin-bottom: 12px; }
        @keyframes login-brand-swap {
            0%        { opacity: 0; transform: translateY(6px); }
            8%, 42%   { opacity: 1; transform: translateY(0); }
            50%, 100% { opacity: 0; transform: translateY(-6px); }
        }
        @media (prefers-reduced-motion: reduce) {
            .login-brand-slide { animation: none; }
            .login-brand-slide-a { opacity: 1; }
            .login-brand-slide-b { display: none; }
        }

        .form-control {
            background-color: #f8fafc !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 10px !important;
            padding: 12px 16px 12px 42px !important;
            font-weight: 500;
            color: var(--mei-text-dark);
        }

        .form-control:focus {
            background-color: #ffffff !important;
            border-color: var(--mei-green-brand) !important;
            box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.15) !important;
        }

        .input-group-text-custom {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            color: #64748b;
            font-size: 18px;
        }

        .btn-mei-primary {
            background-color: var(--mei-green-brand);
            border-color: var(--mei-green-brand);
            color: #ffffff;
            font-weight: 700;
            border-radius: 10px;
            padding: 12px;
            transition: all 0.2s ease-in-out;
        }

        .btn-mei-primary:hover {
            background-color: var(--mei-green-dark);
            border-color: var(--mei-green-dark);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(5, 150, 105, 0.25);
        }

        .form-check-input:checked {
            background-color: var(--mei-green-brand);
            border-color: var(--mei-green-brand);
        }
    </style>
    @include('partials.theme')
</head>
<body>
    <button type="button" class="theme-toggle floating" aria-label="Ganti tema" title="Tema gelap"><i class="bi bi-moon-stars-fill"></i></button>

    <div class="login-card">
        <!-- Header Brand -->
        <div class="login-header">
            <div class="login-brand-rotator">
                <!-- Slide A: PT MUDALAYA ENERGY INDONESIA (logo hijau) -->
                <div class="login-brand-slide login-brand-slide-a">
                    <img src="{{ asset('images/mgi-logo.png') }}" alt="Logo PT Mudalaya Energy Indonesia">
                    <h5 class="fw-extrabold text-dark m-0" style="font-weight: 800; letter-spacing: -0.5px;">
                        PT MUDALAYA ENERGY INDONESIA
                    </h5>
                </div>
                <!-- Slide B: PT MUTIARA GLOBAL INDUSTRY (logo oranye) -->
                <div class="login-brand-slide login-brand-slide-b">
                    <img src="{{ asset('images/mei-logo.png') }}" alt="Logo PT Mutiara Global Industry">
                    <h5 class="fw-extrabold text-dark m-0" style="font-weight: 800; letter-spacing: -0.5px;">
                        PT MUTIARA GLOBAL INDUSTRY
                    </h5>
                </div>
            </div>
            <small class="text-secondary fw-semibold mt-1 d-block" style="font-size: 0.8rem;">
                "The great people's energy"
            </small>
        </div>

        <!-- Form Section -->
        <div class="p-4 pt-3">
            <!-- Alert Error Validation -->
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 small py-2 px-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Email atau kata sandi tidak cocok.
                    <button type="button" class="btn-close p-2" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Input Email -->
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold mb-1">EMAIL / USERNAME</label>
                    <div class="position-relative">
                        <i class="bi bi-envelope-fill input-group-text-custom"></i>
                        <input type="email" name="email" class="form-control" placeholder="masukkan email..." value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <!-- Input Password -->
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold mb-1">KATA SANDI</label>
                    <div class="position-relative">
                        <i class="bi bi-lock-fill input-group-text-custom"></i>
                        <input type="password" name="password" class="form-control" placeholder="masukkan kata sandi..." required>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label small text-secondary fw-semibold" for="remember">
                            Ingat Saya
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-mei-primary w-100 mb-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i> MASUK KE DASHBOARD
                </button>
            </form>
        </div>

        <!-- Footer Card -->
        <div class="bg-light p-3 text-center border-top border-slate-200">
            <small class="text-muted" style="font-size: 0.75rem;">
                &copy; {{ date('Y') }} PT Mudalaya Energy Indonesia. All rights reserved.
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>