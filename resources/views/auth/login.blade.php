<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập · NhanSu360</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
      tailwind.config = { theme: { extend: { colors: {
        ink:'#1E2532', slate925:'#171B26', brand:{DEFAULT:'#4338CA',light:'#EEEDFE'},
        accent:'#0D9488', canvas:'#F7F7F5', line:'#E5E4DF',
        danger:{DEFAULT:'#B91C1C', light:'#FDECEC'}
      }, fontFamily: { display:['"Space Grotesk"','sans-serif'], body:['Inter','sans-serif'] } } } }
    </script>
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-canvas min-h-screen flex">

    {{-- Left: brand panel --}}
    <div class="hidden lg:flex w-1/2 bg-slate925 text-white flex-col justify-between p-12 relative overflow-hidden">
        <div class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-brand/20 blur-3xl"></div>
        <div class="absolute -left-10 bottom-10 w-72 h-72 rounded-full bg-accent/10 blur-3xl"></div>

        <div class="flex items-center gap-2 relative">
            <div class="w-8 h-8 rounded-md bg-brand flex items-center justify-center">
                <span class="font-display font-bold text-sm">N</span>
            </div>
            <span class="font-display font-semibold tracking-tight">NhanSu360</span>
        </div>

        <div class="relative">
            <p class="font-display text-3xl leading-snug max-w-sm">Chấm công đúng nơi,<br>đúng người, đúng lúc.</p>
            <p class="text-slate-400 text-sm mt-4 max-w-sm">Vị trí, ảnh xác nhận và thời gian máy chủ — mọi lượt chấm công đều có bằng chứng đối chiếu rõ ràng.</p>
        </div>

        <p class="text-slate-500 text-xs relative">© 2026 NhanSu360 — Hệ thống quản lý nhân sự &amp; chấm công</p>
    </div>

    {{-- Right: form --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
        <div class="w-full max-w-sm">
            <h1 class="font-display text-2xl font-semibold text-ink">Đăng nhập</h1>
            <p class="text-sm text-ink/50 mt-1 mb-8">Nhập thông tin tài khoản để tiếp tục.</p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-danger-light border border-danger/20 text-danger text-sm px-3.5 py-2.5">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ url('/login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-ink mb-1.5">Tên đăng nhập</label>
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="vd: nguyenngocson"
                           class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink mb-1.5">Mật khẩu</label>
                    <input type="password" name="password" placeholder="••••••••"
                           class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand">
                </div>

                <div class="flex items-center justify-between text-sm pt-1">
                    <label class="flex items-center gap-2 text-ink/70">
                        <input type="checkbox" name="remember" class="rounded border-line text-brand focus:ring-brand">
                        Ghi nhớ đăng nhập
                    </label>
                    <a href="#" class="text-brand font-medium hover:underline">Quên mật khẩu?</a>
                </div>

                <button type="submit"
                        class="w-full bg-brand hover:bg-brand/90 text-white font-medium text-sm py-2.5 rounded-lg transition-colors">
                    Đăng nhập
                </button>
            </form>
        </div>
    </div>
</body>
</html>
