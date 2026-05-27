<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SmartStock Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous" />
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('head-scripts')
</head>
<body class="h-full" style="background-color:#F8FAFC; font-family:'Inter',sans-serif;" x-data="appShell()" x-cloak>

    <div class="flex h-full">

        {{-- ── Sidebar ──────────────────────────────────────────── --}}
        <aside
            class="app-sidebar fixed inset-y-0 left-0 z-50 flex flex-col"
            :style="{ width: sidebarOpen ? '256px' : '64px' }"
        >
            {{-- Logo row --}}
            <div class="flex items-center gap-3 px-4 overflow-hidden" :class="sidebarOpen ? 'justify-start' : 'justify-center'" style="height:78px; border-bottom:1px solid rgba(255,255,255,0.14); flex-shrink:0;">
                <img src="{{ asset('smartstockpro.png') }}"
                     alt="SmartStock Pro"
                     class="object-contain transition-all duration-200 ease-out"
                     :style="sidebarOpen ? 'height:52px; width:52px;' : 'height:34px; width:34px;'">
                <div x-show="sidebarOpen"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-x-2"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     class="min-w-0">
                    <p style="font-size:18px; line-height:1.1; font-weight:700; color:#FFFFFF; letter-spacing:0.01em; white-space:nowrap;">SmartStock Pro</p>
                    <p style="font-size:12px; line-height:1.3; font-weight:500; color:rgba(232,240,255,0.9); white-space:nowrap;">PT Maju Bersama Digital</p>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto overflow-x-hidden py-5 px-3 space-y-0.5"
                 style="scrollbar-width:thin; scrollbar-color:rgba(255,255,255,0.1) transparent;">

                <a href="{{ route('dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   :title="!sidebarOpen ? 'Dashboard' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span x-show="sidebarOpen" style="white-space:nowrap;">Dashboard</span>
                </a>

                <div x-show="sidebarOpen" class="sidebar-section">Inventaris</div>
                <div x-show="!sidebarOpen" style="height:4px;"></div>

                <a href="{{ route('products.index') }}"
                   class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
                   :title="!sidebarOpen ? 'Produk' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V7"/>
                    </svg>
                    <span x-show="sidebarOpen" style="white-space:nowrap;">Produk</span>
                </a>
                <a href="{{ route('transactions.index') }}"
                   class="sidebar-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}"
                   :title="!sidebarOpen ? 'Transaksi' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span x-show="sidebarOpen" style="white-space:nowrap;">Transaksi</span>
                </a>
                <a href="{{ route('transfers.index') }}"
                   class="sidebar-link {{ request()->routeIs('transfers.*') ? 'active' : '' }}"
                   :title="!sidebarOpen ? 'Transfer Gudang' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span x-show="sidebarOpen" style="white-space:nowrap;">Transfer Gudang</span>
                </a>

                <div x-show="sidebarOpen" class="sidebar-section">Master Data</div>
                <div x-show="!sidebarOpen" style="height:4px;"></div>

                <a href="{{ route('categories.index') }}"
                   class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                   :title="!sidebarOpen ? 'Kategori' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span x-show="sidebarOpen" style="white-space:nowrap;">Kategori</span>
                </a>
                <a href="{{ route('warehouses.index') }}"
                   class="sidebar-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}"
                   :title="!sidebarOpen ? 'Gudang' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span x-show="sidebarOpen" style="white-space:nowrap;">Gudang</span>
                </a>
                <a href="{{ route('suppliers.index') }}"
                   class="sidebar-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"
                   :title="!sidebarOpen ? 'Supplier' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="sidebarOpen" style="white-space:nowrap;">Supplier</span>
                </a>

                <div x-show="sidebarOpen" class="sidebar-section">Laporan</div>
                <div x-show="!sidebarOpen" style="height:4px;"></div>

                <a href="{{ route('reports.index') }}"
                   class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                   :title="!sidebarOpen ? 'Laporan' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span x-show="sidebarOpen" style="white-space:nowrap;">Laporan</span>
                </a>

                @if(auth()->user()->isAdmin() || auth()->user()->isManagerGudang())
                <div x-show="sidebarOpen" class="sidebar-section">Sistem</div>
                <div x-show="!sidebarOpen" style="height:4px;"></div>
                <a href="{{ route('error-logs.index') }}"
                   class="sidebar-link {{ request()->routeIs('error-logs.*') ? 'active' : '' }}"
                   :title="!sidebarOpen ? 'Log Error' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span x-show="sidebarOpen" style="white-space:nowrap;">Log Error</span>
                </a>
                @endif

                @if(auth()->user()->isAdmin())
                <a href="{{ route('audit-logs.index') }}"
                   class="sidebar-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}"
                   :title="!sidebarOpen ? 'Audit Log' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span x-show="sidebarOpen" style="white-space:nowrap;">Audit Log</span>
                </a>
                <a href="{{ route('users.index') }}"
                   class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                   :title="!sidebarOpen ? 'Pengguna' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span x-show="sidebarOpen" style="white-space:nowrap;">Pengguna</span>
                </a>
                @endif
            </nav>

            {{-- Collapse toggle --}}
            <div class="p-3" style="border-top:1px solid rgba(255,255,255,0.11); flex-shrink:0;">
                <button @click="toggleSidebar()"
                    class="w-full flex items-center justify-center p-2 rounded-lg"
                    style="background:transparent; border:none; color:#C6D6EE; cursor:pointer; transition:all 180ms ease;"
                    onmouseover="this.style.backgroundColor='rgba(255,255,255,0.11)'; this.style.color='#FFFFFF';"
                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='#C6D6EE';">
                    <svg x-show="sidebarOpen" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                    <svg x-show="!sidebarOpen" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </aside>

        {{-- ── Main column ──────────────────────────────────────── --}}
        <div class="app-main-shell flex-1 flex flex-col min-h-screen"
             :style="{ paddingLeft: sidebarOpen ? '256px' : '64px' }">

            {{-- Topbar --}}
            <header class="sticky top-0 z-40 flex items-center justify-between px-8"
                style="height:64px; background:#FFFFFF; border-bottom:1px solid #E5EDF5; box-shadow:0px 1px 2px rgba(0,0,0,0.04);">

                {{-- Left: breadcrumb --}}
                <div class="flex items-center gap-2 min-w-0">
                    <span style="font-size:14px; font-weight:500; color:#061B31; white-space:nowrap;">
                        @yield('page-title', 'Dashboard')
                    </span>
                    @hasSection('breadcrumb')
                    <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:#D4DEE9;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span style="font-size:14px; color:#64748D; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        @yield('breadcrumb')
                    </span>
                    @endif
                </div>

                {{-- Right: notification + user --}}
                <div class="flex items-center gap-2">

                    {{-- Notification Bell --}}
                    <div x-data="notificationBell()" x-init="init()" class="relative">
                        <button @click="toggleDropdown()"
                            class="relative flex items-center justify-center w-9 h-9 rounded"
                            style="background:transparent; border:none; color:#64748D; cursor:pointer; transition:background-color 120ms ease;"
                            onmouseover="this.style.backgroundColor='#F8FAFC'; this.style.color='#061B31';"
                            onmouseout="this.style.backgroundColor='transparent'; this.style.color='#64748D';">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span x-show="unreadCount > 0" x-text="unreadCount"
                                class="absolute top-1 right-1 flex items-center justify-center"
                                style="min-width:16px; height:16px; background:#533AFD; color:#FFFFFF; font-size:10px; font-weight:600; border-radius:8px; padding:0 4px; line-height:1;"></span>
                        </button>

                        <div x-show="open" x-cloak @click.outside="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="absolute right-0 mt-1"
                            style="width:320px; background:#FFFFFF; border:1px solid #D4DEE9; border-radius:6px; box-shadow:0px 10px 40px rgba(0,0,0,0.1); overflow:hidden; z-index:50;">
                            <div class="flex items-center justify-between px-4 py-3" style="border-bottom:1px solid #E5EDF5;">
                                <span style="font-size:14px; font-weight:500; color:#061B31;">Notifikasi</span>
                                <button @click="markAllRead()" style="font-size:12px; color:#533AFD; background:none; border:none; cursor:pointer; padding:0;">Tandai semua dibaca</button>
                            </div>
                            <div style="max-height:280px; overflow-y:auto;">
                                <template x-if="notifications.length === 0">
                                    <div class="px-4 py-8 text-center" style="font-size:14px; color:#64748D;">Tidak ada notifikasi baru</div>
                                </template>
                                <template x-for="n in notifications" :key="n.id">
                                    <div class="px-4 py-3 cursor-pointer"
                                        :style="!n.read_at ? 'background:#FAFBFE;' : ''"
                                        style="border-bottom:1px solid #F1F5F9; transition:background-color 100ms ease;"
                                        @mouseover="$el.style.backgroundColor='#F8FAFC'"
                                        @mouseout="$el.style.backgroundColor = !n.read_at ? '#FAFBFE' : ''"
                                        @click="markRead(n.id)">
                                        <div class="flex items-start gap-3">
                                            <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0"
                                                :style="!n.read_at ? 'background:#533AFD;' : 'background:transparent;'"></div>
                                            <div>
                                                <p style="font-size:14px; font-weight:500; color:#061B31;" x-text="n.data.title"></p>
                                                <p style="font-size:12px; color:#64748D; margin-top:2px;" x-text="n.data.message"></p>
                                                <p style="font-size:11px; color:#B8CCDB; margin-top:4px;" x-text="n.created_at"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div style="width:1px; height:24px; background:#E5EDF5;"></div>

                    {{-- User menu --}}
                    <div x-data="{ menuOpen: false }" class="relative">
                        <button @click="menuOpen = !menuOpen"
                            class="flex items-center gap-2.5 px-3 py-1.5 rounded"
                            style="background:transparent; border:none; cursor:pointer; transition:background-color 120ms ease;"
                            onmouseover="this.style.backgroundColor='#F8FAFC';"
                            onmouseout="this.style.backgroundColor='transparent';">
                            <div class="w-7 h-7 rounded flex items-center justify-center flex-shrink-0"
                                 style="background:#533AFD; font-size:11px; font-weight:600; color:#FFFFFF;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <div class="text-left hidden sm:block">
                                <p style="font-size:13px; font-weight:500; color:#061B31; line-height:1.3;">{{ auth()->user()->name }}</p>
                                <p style="font-size:11px; color:#64748D; line-height:1.3;">{{ auth()->user()->role }}</p>
                            </div>
                            <svg class="w-3.5 h-3.5" style="color:#B8CCDB;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="menuOpen" x-cloak @click.outside="menuOpen = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="absolute right-0 mt-1"
                            style="min-width:200px; background:#FFFFFF; border:1px solid #D4DEE9; border-radius:6px; box-shadow:0px 10px 40px rgba(0,0,0,0.1); overflow:hidden; z-index:50; padding:8px 0;">
                            <div class="px-4 py-3" style="border-bottom:1px solid #E5EDF5;">
                                <p style="font-size:13px; font-weight:500; color:#061B31;">{{ auth()->user()->name }}</p>
                                <p style="font-size:12px; color:#64748D; margin-top:2px;">{{ auth()->user()->email }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5"
                                    style="background:none; border:none; cursor:pointer; font-size:14px; color:#50617A; text-align:left; transition:background-color 100ms ease;"
                                    onmouseover="this.style.backgroundColor='#FEF2F2'; this.style.color='#DC2626';"
                                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='#50617A';">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Flash messages --}}
            @if(session('success') || session('error') || session('warning') || session('info'))
            <div class="px-8 pt-5" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                @if(session('success'))
                <div class="alert-success">
                    <svg class="w-5 h-5 flex-shrink-0" style="color:#10B981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                    <button @click="show = false" class="ml-auto" style="background:none; border:none; cursor:pointer; color:#065F46; opacity:0.6;">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @endif
                @if(session('error'))
                <div class="alert-error">
                    <svg class="w-5 h-5 flex-shrink-0" style="color:#EF4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>{{ session('error') }}</span>
                    <button @click="show = false" class="ml-auto" style="background:none; border:none; cursor:pointer; color:#991B1B; opacity:0.6;">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @endif
                @if(session('warning'))
                <div class="alert-warning">
                    <svg class="w-5 h-5 flex-shrink-0" style="color:#F59E0B;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('warning') }}</span>
                </div>
                @endif
                @if(session('info'))
                <div class="alert-info">
                    <svg class="w-5 h-5 flex-shrink-0" style="color:#533AFD;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('info') }}</span>
                </div>
                @endif
            </div>
            @endif

            {{-- Page content --}}
            <main class="flex-1 px-8 py-8">
                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="px-8 py-4" style="border-top:1px solid #E5EDF5;">
                <p style="font-size:12px; color:#B8CCDB;">SmartStock Pro &copy; {{ date('Y') }} — PT Maju Bersama Digital. Seluruh hak dilindungi.</p>
            </footer>
        </div>
    </div>

    {{-- External scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
    /** Shared Leaflet helpers — wait for L + layout, then init maps reliably */
    window.SmartStockMaps = {
        ready: function (fn) {
            function attempt() {
                if (typeof L === 'undefined') {
                    setTimeout(attempt, 40);
                    return;
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function () {
                        requestAnimationFrame(fn);
                    });
                } else {
                    requestAnimationFrame(fn);
                }
            }
            attempt();
        },
        createMap: function (elementId, options) {
            options = options || {};
            var el = document.getElementById(elementId);
            if (!el) return null;

            var map = L.map(el, {
                zoomControl: options.zoomControl !== false,
                scrollWheelZoom: !!options.scrollWheelZoom,
                dragging: options.dragging !== false,
                attributionControl: true,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19,
            }).addTo(map);

            var delays = [0, 100, 350];
            delays.forEach(function (ms) {
                setTimeout(function () { map.invalidateSize(true); }, ms);
            });

            return map;
        },
        warehouseIcon: function () {
            return L.divIcon({
                html: '<div style="width:28px;height:28px;background:#533AFD;border:2px solid #FFFFFF;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(83,58,253,0.35);"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg></div>',
                className: 'ss-map-marker',
                iconSize: [28, 28],
                iconAnchor: [14, 28],
                popupAnchor: [0, -28],
            });
        },
        fitView: function (map, latLngs) {
            if (!latLngs || latLngs.length === 0) {
                map.setView([-2.548926, 118.0148634], 5);
                return;
            }
            if (latLngs.length === 1) {
                map.setView(latLngs[0], 12);
                return;
            }
            map.fitBounds(L.latLngBounds(latLngs), { padding: [32, 32], maxZoom: 12 });
        },
    };

    function appShell() {
        return {
            sidebarOpen: localStorage.getItem('ss_sidebar') !== 'false',
            init() {
                this.$watch('sidebarOpen', val => localStorage.setItem('ss_sidebar', val));
            },
            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
            }
        };
    }

    function notificationBell() {
        return {
            open: false,
            notifications: [],
            unreadCount: 0,
            init() {
                this.fetchNotifications();
                setInterval(() => this.fetchNotifications(), 12000);
            },
            async fetchNotifications() {
                try {
                    const r = await fetch('/api/notifications/unread', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const d = await r.json();
                    this.notifications = d.notifications ?? [];
                    this.unreadCount   = d.unread_count ?? 0;
                } catch (_) {}
            },
            toggleDropdown() { this.open = !this.open; },
            async markRead(id) {
                await fetch(`/api/notifications/${id}/read`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }
                });
                this.fetchNotifications();
            },
            async markAllRead() {
                await fetch('/api/notifications/read-all', {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }
                });
                this.open = false;
                this.fetchNotifications();
            }
        };
    }
    </script>

    @stack('scripts')
</body>
</html>
