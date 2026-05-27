<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — SmartStock Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full" style="background-color:#F8FAFC; font-family:'Inter',sans-serif;">

    <div class="min-h-full flex">

        {{-- Left panel: branding / hero --}}
        <div class="hidden lg:flex lg:flex-col lg:w-[480px] xl:w-[560px] flex-shrink-0 relative overflow-hidden p-12"
             style="background: linear-gradient(135deg, #533AFD 0%, #FF6118 100%);">

            {{-- Noise/texture overlay --}}
            <div class="absolute inset-0" style="background:rgba(0,0,0,0.08);"></div>

            <div class="relative z-10 flex flex-col h-full">
                {{-- Logo --}}
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded flex items-center justify-center" style="background:rgba(255,255,255,0.2); backdrop-filter:blur(8px);">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V7"/>
                        </svg>
                    </div>
                    <span style="font-size:18px; font-weight:600; color:#FFFFFF;">SmartStock Pro</span>
                </div>

                {{-- Headline --}}
                <div class="mt-auto mb-auto">
                    <h1 style="font-size:40px; font-weight:300; color:#FFFFFF; line-height:1.2; margin-bottom:16px;">
                        Kelola inventaris<br>lebih cerdas.
                    </h1>
                    <p style="font-size:16px; color:rgba(255,255,255,0.8); line-height:1.6; max-width:360px;">
                        Platform manajemen stok terpadu untuk 5 gudang di seluruh Indonesia. Real-time, akurat, dan mudah digunakan.
                    </p>
                </div>

                {{-- Feature bullets --}}
                <div class="space-y-3 mt-8">
                    @foreach([
                        ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'text' => 'Pantau stok real-time di semua gudang'],
                        ['icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'text' => 'Transfer antar gudang dengan satu klik'],
                        ['icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'text' => 'Laporan PDF otomatis siap ekspor'],
                    ] as $feat)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.15);">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feat['icon'] }}"/>
                            </svg>
                        </div>
                        <span style="font-size:14px; color:rgba(255,255,255,0.85);">{{ $feat['text'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right panel: login form --}}
        <div class="flex-1 flex items-center justify-center p-6 sm:p-12">
            <div class="w-full max-w-sm">

                {{-- Mobile logo --}}
                <div class="flex items-center gap-2.5 mb-8 lg:hidden">
                    <div class="w-8 h-8 rounded flex items-center justify-center" style="background:#533AFD;">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V7"/>
                        </svg>
                    </div>
                    <span style="font-size:16px; font-weight:600; color:#061B31;">SmartStock Pro</span>
                </div>

                {{-- Header --}}
                <div class="mb-8">
                    <h2 style="font-size:28px; font-weight:300; color:#061B31; line-height:1.2;">Masuk ke akun Anda</h2>
                    <p style="font-size:14px; color:#64748D; margin-top:8px;">Gunakan kredensial yang diberikan administrator.</p>
                </div>

                {{-- Error alert --}}
                @if($errors->any())
                <div class="alert-error mb-6">
                    <svg class="w-5 h-5 flex-shrink-0" style="color:#EF4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('login.post') }}" x-data="{ showPw: false, loading: false }" @submit="loading = true">
                    @csrf

                    <div class="space-y-5">

                        {{-- Email --}}
                        <div>
                            <label for="email" class="ss-label">
                                Alamat Email
                            </label>
                            <input
                                id="email" type="email" name="email"
                                value="{{ old('email') }}"
                                required autocomplete="email"
                                placeholder="nama@perusahaan.com"
                                class="ss-input @error('email') error @enderror"
                            >
                        </div>

                        {{-- Password --}}
                        <div>
                            <label for="password" class="ss-label">Kata Sandi</label>
                            <div class="relative">
                                <input
                                    id="password"
                                    :type="showPw ? 'text' : 'password'"
                                    name="password"
                                    required autocomplete="current-password"
                                    placeholder="Minimal 8 karakter"
                                    class="ss-input"
                                    style="padding-right:44px;"
                                >
                                <button type="button" @click="showPw = !showPw"
                                    class="absolute right-3 top-1/2 -translate-y-1/2"
                                    style="background:none; border:none; cursor:pointer; color:#64748D; padding:4px;">
                                    <svg x-show="!showPw" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showPw" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Remember --}}
                        <div class="flex items-center gap-2.5">
                            <input type="checkbox" id="remember" name="remember"
                                style="width:16px; height:16px; border-radius:3px; border:1px solid #D4DEE9; accent-color:#533AFD; cursor:pointer;">
                            <label for="remember" style="font-size:14px; color:#50617A; cursor:pointer;">Ingat saya selama 30 hari</label>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" :disabled="loading"
                        class="btn-primary w-full mt-6"
                        :style="loading ? 'opacity:0.7; cursor:not-allowed;' : ''">
                        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                    </button>
                </form>

                {{-- Demo accounts --}}
                <div class="mt-8 p-4" style="background:#F8FAFC; border:1px solid #E5EDF5; border-radius:5px;">
                    <p style="font-size:12px; font-weight:500; color:#64748D; margin-bottom:10px; text-transform:uppercase; letter-spacing:0.05em;">Akun Demo</p>
                    <div class="space-y-2">
                        @foreach([
                            ['email' => 'admin@smartstock.id',   'role' => 'Admin',          'color' => '#533AFD', 'bg' => '#E8E9FF'],
                            ['email' => 'manager@smartstock.id', 'role' => 'Manajer Gudang', 'color' => '#065F46', 'bg' => '#D1FAE5'],
                            ['email' => 'staf@smartstock.id',    'role' => 'Staf Gudang',    'color' => '#92400E', 'bg' => '#FEF3C7'],
                        ] as $demo)
                        <div class="flex items-center justify-between">
                            <span style="font-size:13px; color:#061B31;">{{ $demo['email'] }}</span>
                            <span class="badge" :style="`background: {{ $demo['bg'] }}; color: {{ $demo['color'] }}; border: none`">{{ $demo['role'] }}</span>
                        </div>
                        @endforeach
                        <p style="font-size:11px; color:#B8CCDB; margin-top:6px;">Kata sandi: <code style="font-family:monospace; color:#64748D;">password</code></p>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
