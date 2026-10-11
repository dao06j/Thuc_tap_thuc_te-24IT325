@extends('layouts.app')

@section('title', 'Hồ sơ cá nhân')
@section('page-title', 'Hồ sơ cá nhân')
@section('page-subtitle', 'Thông tin nhân sự của bạn')

@section('content')
<div class="space-y-4">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- ==== HEADER: full width, không chừa khoảng trống ==== --}}
    <div class="bg-white rounded-2xl border border-line p-6 flex flex-col sm:flex-row sm:items-center gap-5">
        <div class="w-20 h-20 rounded-full bg-slate-925 text-white flex items-center justify-center text-2xl font-display font-semibold shrink-0 overflow-hidden">
            @if ($me?->photo_path)
                <img src="{{ asset('storage/' . $me->photo_path) }}" alt="{{ $me->name }}" class="w-full h-full object-cover">
            @else
                {{ mb_substr($me?->name ?? 'D', 0, 1) }}
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <h2 class="font-display font-semibold text-xl">{{ $me?->name ?? 'Chưa có hồ sơ' }}</h2>
            <p class="text-sm text-ink/50 mt-0.5">{{ $me?->position?->name ?? '—' }} · {{ $me?->department?->name ?? '—' }}</p>
            <div class="flex flex-wrap items-center gap-2 mt-2.5">
                <span class="text-xs font-mono font-medium px-2.5 py-1 rounded-full bg-brand-light text-brand">{{ $me?->code ?? '—' }}</span>
                @if ($me)
                    @php
                        $statusMap = [
                            'active' => ['Đang làm việc', 'bg-accent-light text-accent'],
                            'resigned' => ['Đã nghỉ việc', 'bg-canvas text-ink/40'],
                            'suspended' => ['Tạm ngưng', 'bg-warn-light text-warn'],
                        ];
                        [$statusLabel, $statusClass] = $statusMap[$me->status] ?? ['—', 'bg-canvas text-ink/40'];
                    @endphp
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button" onclick="document.getElementById('changePasswordModal').classList.remove('hidden')"
                    class="text-sm font-medium px-4 py-2.5 rounded-lg border border-line text-ink/70 hover:bg-canvas">
                Đổi mật khẩu
            </button>
            <button type="button" onclick="document.getElementById('editProfileModal').classList.remove('hidden')"
                    class="text-sm font-medium px-4 py-2.5 rounded-lg bg-brand text-white hover:bg-brand/90">
                Cập nhật hồ sơ
            </button>
        </div>
    </div>

    {{-- ==== THÔNG TIN CÔNG VIỆC (chỉ HR sửa được) — full width grid ==== --}}
    <div class="bg-white rounded-2xl border border-line p-6">
        <h3 class="font-display font-semibold text-sm text-ink/60 uppercase tracking-wide mb-4">Thông tin công việc</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-5">
            <div>
                <p class="text-xs text-ink/40">Phòng ban</p>
                <p class="text-sm font-medium mt-1">{{ $me?->department?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-ink/40">Chức vụ</p>
                <p class="text-sm font-medium mt-1">{{ $me?->position?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-ink/40">Ngày vào làm</p>
                <p class="text-sm font-medium mt-1">{{ $me?->hire_date?->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-ink/40">Loại hợp đồng</p>
                @php
                    $contractMap = ['probation' => 'Thử việc', 'official' => 'Chính thức', 'seasonal' => 'Thời vụ'];
                @endphp
                <p class="text-sm font-medium mt-1">{{ $contractMap[$me?->contract_type] ?? '—' }}</p>
            </div>
        </div>
        <p class="text-xs text-ink/30 mt-4">Các thông tin này do phòng Nhân sự quản lý — liên hệ HR nếu cần điều chỉnh.</p>
    </div>

    {{-- ==== THÔNG TIN CÁ NHÂN (tự sửa được) — full width grid ==== --}}
    <div class="bg-white rounded-2xl border border-line p-6">
        <h3 class="font-display font-semibold text-sm text-ink/60 uppercase tracking-wide mb-4">Thông tin cá nhân</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div>
                <p class="text-xs text-ink/40">Số CCCD</p>
                <p class="text-sm font-medium mt-1">{{ $me?->national_id ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-ink/40">Ngày sinh</p>
                <p class="text-sm font-medium mt-1">{{ $me?->date_of_birth?->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-ink/40">Số điện thoại</p>
                <p class="text-sm font-medium mt-1">{{ $me?->phone ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-ink/40">Email</p>
                <p class="text-sm font-medium mt-1 truncate">{{ $me?->email ?? '—' }}</p>
            </div>
            <div class="sm:col-span-2 lg:col-span-2">
                <p class="text-xs text-ink/40">Địa chỉ</p>
                <p class="text-sm font-medium mt-1">{{ $me?->address ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ==== MODAL: Cập nhật hồ sơ ==== --}}
<div id="editProfileModal" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl border border-line w-full max-w-2xl p-5 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-ink">Cập nhật hồ sơ</h2>
            <button type="button" onclick="document.getElementById('editProfileModal').classList.add('hidden')" class="text-ink/60 hover:text-ink text-2xl leading-none">×</button>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            @method('PUT')

            <div class="md:col-span-2 flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-slate-925 text-white flex items-center justify-center text-xl font-display font-semibold overflow-hidden shrink-0">
                    @if ($me?->photo_path)
                        <img src="{{ asset('storage/' . $me->photo_path) }}" alt="" class="w-full h-full object-cover">
                    @else
                        {{ mb_substr($me?->name ?? 'D', 0, 1) }}
                    @endif
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-ink/60 mb-1">Ảnh đại diện</label>
                    <input type="file" name="photo" accept="image/*" class="w-full text-sm">
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-ink/60 mb-1">Họ và tên</label>
                <input type="text" name="name" value="{{ old('name', $me?->name) }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>

            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Số CCCD</label>
                <input type="text" name="national_id" value="{{ old('national_id', $me?->national_id) }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>

            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Ngày sinh</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($me?->date_of_birth)->format('Y-m-d')) }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>

            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Số điện thoại</label>
                <input type="text" name="phone" value="{{ old('phone', $me?->phone) }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>

            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $me?->email) }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-ink/60 mb-1">Địa chỉ</label>
                <input type="text" name="address" value="{{ old('address', $me?->address) }}" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>

            <div class="md:col-span-2 flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('editProfileModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm font-medium border border-line text-ink/70 hover:bg-canvas">
                    Hủy
                </button>
                <button type="submit" class="bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand/90">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ==== MODAL: Đổi mật khẩu ==== --}}
<div id="changePasswordModal" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl border border-line w-full max-w-md p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-ink">Đổi mật khẩu</h2>
            <button type="button" onclick="document.getElementById('changePasswordModal').classList.add('hidden')" class="text-ink/60 hover:text-ink text-2xl leading-none">×</button>
        </div>

        <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Mật khẩu hiện tại</label>
                <input type="password" name="current_password" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Mật khẩu mới</label>
                <input type="password" name="password" required minlength="6" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Xác nhận mật khẩu mới</label>
                <input type="password" name="password_confirmation" required minlength="6" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('changePasswordModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm font-medium border border-line text-ink/70 hover:bg-canvas">
                    Hủy
                </button>
                <button type="submit" class="bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand/90">
                    Đổi mật khẩu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection