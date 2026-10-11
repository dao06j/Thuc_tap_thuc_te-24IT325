@extends('layouts.app')

@section('title', 'Quản lý tài khoản')
@section('page-title', 'Quản lý tài khoản')
@section('page-subtitle', 'Danh sách tài khoản nhân viên và trạng thái hoạt động')

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

    @if (auth()->user()->role === 'hr')
        <div class="flex justify-end">
            <button type="button" onclick="document.getElementById('createAccountModal').classList.remove('hidden')" class="bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand/90">
                Tạo tài khoản
            </button>
        </div>

        <div id="createAccountModal" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl border border-line w-full max-w-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-ink">Tạo tài khoản mới</h2>
                    <button type="button" onclick="document.getElementById('createAccountModal').classList.add('hidden')" class="text-ink/60 hover:text-ink text-2xl leading-none">×</button>
                </div>

                <form action="{{ route('admin.accounts.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-ink/60 mb-1">Tài khoản</label>
                        <input type="text" name="username" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="VD: nv02">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink/60 mb-1">Mật khẩu</label>
                        <input type="password" name="password" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Mật khẩu">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink/60 mb-1">Nhân viên chưa có tài khoản</label>
                        <select name="employee_id" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            <option value="">-- Chọn nhân viên --</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->code }} - {{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink/60 mb-1">Vai trò</label>
                        <select name="role" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            <option value="employee">Nhân viên</option>
                            <option value="hr">HR</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 flex justify-end gap-2 pt-2">
                        <button type="button" onclick="document.getElementById('createAccountModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm font-medium border border-line text-ink/70 hover:bg-canvas">
                            Hủy
                        </button>
                        <button type="submit" class="bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand/90">
                            Tạo tài khoản
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-line overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 p-5 border-b border-line">
            <h2 class="text-lg font-semibold text-ink">Danh sách tài khoản</h2>
            <form action="{{ route('admin.accounts') }}" method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Tìm theo tên nhân viên..." class="border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand w-64">
                <button type="submit" class="bg-slate-900 text-white px-3 py-2 rounded-lg text-sm hover:bg-slate-800">
                    Tìm kiếm
                </button>
            </form>
            <div class="text-sm text-ink/60">
                Tổng: {{ $users->count() }} tài khoản
            </div>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wide text-ink/40 border-b border-line">
                    <th class="px-5 py-3 font-medium">Mã NV</th>
                    <th class="px-5 py-3 font-medium">Họ tên</th>
                    <th class="px-5 py-3 font-medium">Tài khoản</th>
                    <th class="px-5 py-3 font-medium">Trạng thái TK</th>
                    <th class="px-5 py-3 font-medium text-right">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($users as $user)
                    @php
                        $employee = $user->employee;
                        $isLocked = $user->isLocked();
                    @endphp
                    <tr class="hover:bg-canvas/60 align-middle">
                        <td class="px-5 py-4 font-mono text-ink/70">{{ $employee?->code ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-slate-925 text-white flex items-center justify-center text-xs font-display font-semibold">
                                    {{ $employee ? mb_substr($employee->name, 0, 1) : mb_substr($user->username, 0, 1) }}
                                </div>
                                <span class="font-medium">{{ $employee?->name ?? 'Không gắn nhân viên' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-ink/70 font-medium">{{ $user->username }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $isLocked ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $isLocked ? 'Bị khóa' : 'Hoạt động' }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                @if ((int) auth()->id() !== (int) $user->id)
                                    <form action="{{ route('admin.accounts.toggle-status', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-2 rounded-lg text-xs font-semibold {{ $isLocked ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-amber-500 text-white hover:bg-amber-600' }}">
                                            {{ $isLocked ? 'Mở khóa' : 'Khóa TK' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.accounts.delete', $user) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa tài khoản này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-2 rounded-lg text-xs font-semibold bg-red-600 text-white hover:bg-red-700">
                                            Xóa TK
                                        </button>
                                    </form>
                                @else
                                    <span class="px-3 py-2 rounded-lg text-xs font-semibold bg-slate-100 text-slate-500">
                                        Tài khoản của bạn
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-ink/50">Chưa có tài khoản nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
