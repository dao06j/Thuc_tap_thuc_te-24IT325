@extends('layouts.app')

@section('title', 'Quản lý phòng ban')
@section('page-title', 'Quản lý phòng ban')
@section('page-subtitle', 'Danh sách phòng ban và trạng thái hoạt động')

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

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($departments as $department)
            @php
                $isActive = $department->status === 'active';
                $manager = $department->manager;
                $employeeCount = $department->employees->count();
            @endphp

            <div class="bg-white rounded-2xl border border-line p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="w-10 h-10 rounded-lg bg-brand-light flex items-center justify-center">
                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3"/></svg>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="document.getElementById('editDepartmentModal-{{ $department->id }}').classList.remove('hidden')" class="text-ink/40 hover:text-brand text-xs font-medium">Sửa</button>
                        <form action="{{ route('admin.departments.toggle-status', $department) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs font-medium px-2 py-1 rounded {{ $isActive ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $isActive ? 'Khóa' : 'Kích hoạt' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between gap-3">
                    <h3 class="font-display font-semibold text-lg">{{ $department->name }}</h3>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ $isActive ? 'Hoạt động' : 'Khóa' }}
                    </span>
                </div>

                <p class="text-sm text-ink/50 mt-1">Mã: {{ $department->code }}</p>
                <p class="text-sm text-ink/50 mt-0.5">Trưởng phòng: {{ $manager?->name ?? 'Chưa có' }}</p>
                <p class="text-2xl font-display font-semibold mt-3">{{ $employeeCount }}<span class="text-sm text-ink/40 font-body"> nhân viên</span></p>
            </div>

            <div id="editDepartmentModal-{{ $department->id }}" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl border border-line w-full max-w-2xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-ink">Sửa thông tin phòng ban</h2>
                        <button type="button" onclick="document.getElementById('editDepartmentModal-{{ $department->id }}').classList.add('hidden')" class="text-ink/60 hover:text-ink text-2xl leading-none">×</button>
                    </div>

                    <form action="{{ route('admin.departments.update', $department) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf
                        @method('PUT')

                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-ink/60 mb-1">Tên phòng ban</label>
                            <input type="text" name="name" value="{{ $department->name }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-ink/60 mb-1">Trưởng phòng</label>
                            <select name="manager_id" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                <option value="">-- Chọn trưởng phòng --</option>
                                @foreach ($department->employees as $employee)
                                    <option value="{{ $employee->id }}" {{ $department->manager_id == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-ink/60 mb-1">Ngày thành lập</label>
                            <input type="date" name="established_date" value="{{ optional($department->established_date)->format('Y-m-d') }}" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-ink/60 mb-1">Trạng thái</label>
                            <select name="status" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                <option value="active" {{ $department->status === 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="inactive" {{ $department->status === 'inactive' ? 'selected' : '' }}>Khóa</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 flex justify-end gap-2 pt-2">
                            <button type="button" onclick="document.getElementById('editDepartmentModal-{{ $department->id }}').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm font-medium border border-line text-ink/70 hover:bg-canvas">
                                Hủy
                            </button>
                            <button type="submit" class="bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand/90">
                                Cập nhật
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-line bg-white p-8 text-center text-ink/50">
                Chưa có phòng ban nào.
            </div>
        @endforelse

        <button type="button" onclick="document.getElementById('createDepartmentModal').classList.remove('hidden')" class="bg-white rounded-2xl border border-dashed border-line p-5 min-h-[180px] flex flex-col items-center justify-center text-ink/40 hover:border-brand hover:text-brand transition-colors">
            <div class="w-10 h-10 rounded-lg bg-brand-light flex items-center justify-center">
                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            </div>
            <span class="mt-4 text-sm font-medium">Thêm phòng ban</span>
        </button>
    </div>
</div>

<div id="createDepartmentModal" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl border border-line w-full max-w-2xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-ink">Thêm phòng ban mới</h2>
            <button type="button" onclick="document.getElementById('createDepartmentModal').classList.add('hidden')" class="text-ink/60 hover:text-ink text-2xl leading-none">×</button>
        </div>

        <form action="{{ route('admin.departments.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf

            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-ink/60 mb-1">Tên phòng ban</label>
                <input type="text" name="name" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="VD: Phòng Nhân sự">
            </div>

            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Trưởng phòng</label>
                <select name="manager_id" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="">-- Chọn trưởng phòng --</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Ngày thành lập</label>
                <input type="date" name="established_date" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>

            <input type="hidden" name="status" value="active">

            <div class="md:col-span-2 flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('createDepartmentModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm font-medium border border-line text-ink/70 hover:bg-canvas">
                    Hủy
                </button>
                <button type="submit" class="bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand/90">
                    Lưu phòng ban
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
