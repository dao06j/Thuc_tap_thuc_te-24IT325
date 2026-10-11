<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Trang chủ') · NhanSu360</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              ink: '#1E2532',
              slate: {
                925: '#171B26',
                850: '#1F2536',
                750: '#2B3245',
              },
              brand: {
                DEFAULT: '#4338CA',
                dark: '#372DA6',
                light: '#EEEDFE',
              },
              accent: {
                DEFAULT: '#0D9488',
                light: '#E6F6F4',
              },
              warn: { DEFAULT: '#B45309', light: '#FEF3E2' },
              danger: { DEFAULT: '#B91C1C', light: '#FDECEC' },
              canvas: '#F7F7F5',
              line: '#E5E4DF',
            },
            fontFamily: {
              display: ['"Space Grotesk"', 'sans-serif'],
              body: ['Inter', 'sans-serif'],
              mono: ['"IBM Plex Mono"', 'monospace'],
            },
          }
        }
      }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #F7F7F5; color: #1E2532; }
        .font-display { font-family: 'Space Grotesk', sans-serif; }
        .font-mono { font-family: 'IBM Plex Mono', monospace; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: #D9D8D2; border-radius: 8px; }
        .nav-link { transition: background-color .15s ease, color .15s ease; }
        a:focus-visible, button:focus-visible, input:focus-visible, textarea:focus-visible {
            outline: 2px solid #4338CA; outline-offset: 2px;
        }
        @media (prefers-reduced-motion: reduce) {
          * { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }
    </style>
    @stack('head')
</head>
<body class="antialiased">
<div class="flex min-h-screen">

    {{-- SIDEBAR --}}
    <aside class="w-64 shrink-0 bg-slate-925 text-slate-300 flex flex-col">
        <div class="h-16 flex items-center gap-2 px-5 border-b border-slate-750">
            <div class="w-7 h-7 rounded-md bg-brand flex items-center justify-center">
                <span class="font-display font-bold text-white text-sm">N</span>
            </div>
            <span class="font-display font-semibold text-white tracking-tight">NhanSu360</span>
        </div>

        <nav class="flex-1 px-3 py-5 space-y-6 overflow-y-auto">
            <div>
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-2">Cá nhân</p>
                <div class="space-y-0.5">
                    <a href="{{ url('/dashboard') }}" class="nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->is('dashboard') ? 'bg-brand text-white' : 'hover:bg-slate-850 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h3v16H6a2 2 0 01-2-2V6zM11 4h3a2 2 0 012 2v3h-5V4zM16 11h4a2 2 0 012 2v5a2 2 0 01-2 2h-4v-9z"/></svg>
                        Tổng quan &amp; chấm công
                    </a>
                    <a href="{{ url('/attendance/history') }}" class="nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->is('attendance/*') ? 'bg-brand text-white' : 'hover:bg-slate-850 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Lịch sử chấm công
                    </a>
                    <a href="{{ url('/leave') }}" class="nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->is('leave*') ? 'bg-brand text-white' : 'hover:bg-slate-850 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Đơn nghỉ phép
                    </a>
                    <a href="{{ route('payroll.index') }}" class="nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->is('payroll') ? 'bg-brand text-white' : 'hover:bg-slate-850 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 0.895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 2v8m0 0v2m8-6a8 8 0 11-16 0 8 8 0 0116 0z"/></svg>
                        Bảng lương của tôi
                    </a>
                    <a href="{{ url('/profile') }}" class="nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->is('profile') ? 'bg-brand text-white' : 'hover:bg-slate-850 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Hồ sơ cá nhân
                    </a>
                </div>
            </div>

            @if (auth()->check() && auth()->user()->role === 'hr')
                <div>
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-2">Quản trị (Admin/HR)</p>
                    <div class="space-y-0.5">
                        <a href="{{ url('/admin/employees') }}" class="nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->is('admin/employees*') ? 'bg-brand text-white' : 'hover:bg-slate-850 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 100-8 4 4 0 000 8zm6 0a4 4 0 10-3.4-6.1"/></svg>
                            Nhân viên
                        </a>
                        <a href="{{ url('/admin/attendance') }}" class="nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->is('admin/attendance*') ? 'bg-brand text-white' : 'hover:bg-slate-850 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Chấm công
                        </a>
                        <a href="{{ route('admin.payrolls') }}" class="nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->is('admin/payrolls*') ? 'bg-brand text-white' : 'hover:bg-slate-850 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h.01M11 15h2m-8 5h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Quản lý lương
                        </a>
                        <a href="{{ url('/admin/departments') }}" class="nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->is('admin/departments*') ? 'bg-brand text-white' : 'hover:bg-slate-850 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3m8-14h.01M11 11h.01M11 15h.01M7 7h.01M7 11h.01M7 15h.01M15 7h.01M15 11h.01M15 15h.01"/></svg>
                            Phòng ban
                        </a>
                        <a href="{{ url('/admin/accounts') }}" class="nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->is('admin/accounts*') ? 'bg-brand text-white' : 'hover:bg-slate-850 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8zm-7 8a7 7 0 0114 0v1H5v-1zm16 0h-2v-1a5 5 0 00-5-5M9 18h6"/></svg>
                            Tài khoản
                        </a>
                        <a href="{{ url('/admin/leave-approvals') }}" class="nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->is('admin/leave-approvals*') ? 'bg-brand text-white' : 'hover:bg-slate-850 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Duyệt đơn nghỉ phép
                            @if ($pendingLeaveCount > 0)
                                <span class="ml-auto bg-warn-light text-warn text-[11px] font-semibold px-1.5 py-0.5 rounded-full">{{ $pendingLeaveCount }}</span>
                            @endif
                        </a>
                        <a href="{{ url('/admin/reports') }}" class="nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->is('admin/reports*') ? 'bg-brand text-white' : 'hover:bg-slate-850 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14"/></svg>
                            Thống kê &amp; báo cáo
                        </a>
                    </div>
                </div>
            @endif
        </nav>

        <div class="p-3 border-t border-slate-750">
            @auth
                <p class="px-3 text-xs text-slate-500 mb-1 truncate">{{ auth()->user()->username }} · {{ auth()->user()->role === 'hr' ? 'HR' : 'Nhân viên' }}</p>
            @endauth
            <form action="{{ url('/logout') }}" method="POST">
                @csrf
                <button type="submit" class="nav-link w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-slate-400 hover:bg-slate-850 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Đăng xuất
                </button>
            </form>
        </div>
    </aside>

    {{-- MAIN --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b border-line flex items-center justify-between px-6 shrink-0">
            <div>
                <h1 class="font-display font-semibold text-lg leading-none">@yield('page-title', 'Tổng quan')</h1>
                <p class="text-xs text-ink/50 mt-1">@yield('page-subtitle', ' ')</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-925 text-white flex items-center justify-center text-sm font-display font-semibold">
                    {{ $userInitial ?? 'D' }}
                </div>
            </div>
        </header>

        <main class="flex-1 p-6 overflow-y-auto">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>