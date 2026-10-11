@extends('layouts.app')

@section('title', 'Quản lý nhân viên')
@section('page-title', 'Quản lý nhân viên')
@section('page-subtitle', 'Danh sách toàn bộ nhân viên trong hệ thống')

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

    <div class="bg-white rounded-2xl border border-line overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 p-5 border-b border-line">
            <form action="{{ route('admin.employees') }}" method="GET">
                <input type="text" name="search" value="{{ $search }}" placeholder="Tìm theo tên, mã nhân viên…"
                       class="border border-line rounded-lg text-sm px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-brand"
                       onchange="this.form.submit()">
            </form>
            <button type="button" onclick="document.getElementById('createEmployeeModal').classList.remove('hidden')"
                    class="text-sm font-medium text-white bg-brand rounded-lg px-4 py-2 hover:bg-brand/90 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Thêm hồ sơ nhân viên mới
            </button>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wide text-ink/40 border-b border-line">
                    <th class="px-5 py-3 font-medium">Mã NV</th>
                    <th class="px-5 py-3 font-medium">Họ tên</th>
                    <th class="px-5 py-3 font-medium">Phòng ban</th>
                    <th class="px-5 py-3 font-medium">Chức vụ</th>
                    <th class="px-5 py-3 font-medium">Ngày vào làm</th>
                    <th class="px-5 py-3 font-medium">Trạng thái</th>
                    <th class="px-5 py-3 font-medium text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($employeesList as $employee)
                    @php
                        $isActive = $employee->status === 'active';
                        $statusMap = [
                            'active' => ['Đang làm việc', 'bg-accent-light text-accent'],
                            'resigned' => ['Đã nghỉ việc', 'bg-canvas text-ink/40'],
                            'suspended' => ['Tạm ngưng', 'bg-warn-light text-warn'],
                        ];
                        [$statusLabel, $statusClass] = $statusMap[$employee->status] ?? ['—', 'bg-canvas text-ink/40'];
                    @endphp
                    <tr class="hover:bg-canvas/60">
                        <td class="px-5 py-3 font-mono text-ink/60">{{ $employee->code }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-slate-925 text-white flex items-center justify-center text-xs font-display font-semibold overflow-hidden shrink-0">
                                    @if ($employee->photo_path)
                                        <img src="{{ asset('storage/' . $employee->photo_path) }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        {{ mb_substr($employee->name, 0, 1) }}
                                    @endif
                                </div>
                                <span class="font-medium">{{ $employee->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-ink/70">{{ $employee->department?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-ink/70">{{ $employee->position?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-ink/70">{{ $employee->hire_date?->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <form action="{{ route('admin.employees.toggle-status', $employee) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium px-2.5 py-1.5 rounded-lg {{ $isActive ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                                        {{ $isActive ? 'Đánh dấu nghỉ việc' : 'Đánh dấu đang làm' }}
                                    </button>
                                </form>
                                <button type="button" onclick="document.getElementById('editEmployeeModal-{{ $employee->id }}').classList.remove('hidden')"
                                        class="text-xs font-medium px-2.5 py-1.5 rounded-lg border border-line text-ink/70 hover:bg-canvas">
                                    Sửa
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- MODAL: Sửa toàn bộ thông tin nhân viên --}}
                    <div id="editEmployeeModal-{{ $employee->id }}" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
                        <div class="bg-white rounded-2xl border border-line w-full max-w-3xl p-5 max-h-[90vh] overflow-y-auto">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-ink">Sửa thông tin nhân viên — {{ $employee->code }}</h2>
                                <button type="button" onclick="document.getElementById('editEmployeeModal-{{ $employee->id }}').classList.add('hidden')" class="text-ink/60 hover:text-ink text-2xl leading-none">×</button>
                            </div>

                            <form action="{{ route('admin.employees.update', $employee) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @csrf
                                @method('PUT')

                                <div class="md:col-span-3 flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-full bg-slate-925 text-white flex items-center justify-center text-xl font-display font-semibold overflow-hidden shrink-0">
                                        @if ($employee->photo_path)
                                            <img src="{{ asset('storage/' . $employee->photo_path) }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            {{ mb_substr($employee->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-ink/60 mb-1">Ảnh thẻ</label>
                                        <input type="file" name="photo" accept="image/*" class="w-full text-sm">
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-ink/60 mb-1">Họ và tên</label>
                                    <input type="text" name="name" value="{{ $employee->name }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-ink/60 mb-1">Số CCCD</label>
                                    <input type="text" name="national_id" value="{{ $employee->national_id }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-ink/60 mb-1">Ngày sinh</label>
                                    <input type="date" name="date_of_birth" value="{{ optional($employee->date_of_birth)->format('Y-m-d') }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-ink/60 mb-1">Số điện thoại</label>
                                    <input type="text" name="phone" value="{{ $employee->phone }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-ink/60 mb-1">Email</label>
                                    <input type="email" name="email" value="{{ $employee->email }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-xs font-medium text-ink/60 mb-1">Địa chỉ</label>
                                    <input type="text" name="address" value="{{ $employee->address }}" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                </div>

                                <div class="md:col-span-3 border-t border-line pt-4 mt-1">
                                    <p class="text-xs font-medium text-ink/60 uppercase tracking-wide mb-3">Thông tin công việc</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-ink/60 mb-1">Phòng ban</label>
                                    <select name="department_id" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}" {{ $employee->department_id == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-ink/60 mb-1">Chức vụ</label>
                                    <select name="position_id" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                        @foreach ($positions as $position)
                                            <option value="{{ $position->id }}" {{ $employee->position_id == $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-ink/60 mb-1">Ngày vào công ty</label>
                                    <input type="date" name="hire_date" value="{{ optional($employee->hire_date)->format('Y-m-d') }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-ink/60 mb-1">Loại hợp đồng</label>
                                    <select name="contract_type" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                        <option value="probation" {{ $employee->contract_type === 'probation' ? 'selected' : '' }}>Thử việc</option>
                                        <option value="official" {{ $employee->contract_type === 'official' ? 'selected' : '' }}>Chính thức</option>
                                        <option value="seasonal" {{ $employee->contract_type === 'seasonal' ? 'selected' : '' }}>Thời vụ</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-ink/60 mb-1">Lương cơ bản</label>
                                    <input type="number" name="base_salary" value="{{ $employee->base_salary }}" min="0" step="1000" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-ink/60 mb-1">Trạng thái</label>
                                    <select name="status" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                        <option value="active" {{ $employee->status === 'active' ? 'selected' : '' }}>Đang làm việc</option>
                                        <option value="resigned" {{ $employee->status === 'resigned' ? 'selected' : '' }}>Đã nghỉ việc</option>
                                        <option value="suspended" {{ $employee->status === 'suspended' ? 'selected' : '' }}>Tạm ngưng</option>
                                    </select>
                                </div>

                                <div class="md:col-span-3 flex justify-end gap-2 pt-2">
                                    <button type="button" onclick="document.getElementById('editEmployeeModal-{{ $employee->id }}').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm font-medium border border-line text-ink/70 hover:bg-canvas">
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
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-ink/40">Chưa có nhân viên nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL: Thêm hồ sơ nhân viên mới --}}
<div id="createEmployeeModal" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl border border-line w-full max-w-3xl p-5 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-ink">Thêm hồ sơ nhân viên mới</h2>
            <button type="button" onclick="document.getElementById('createEmployeeModal').classList.add('hidden')" class="text-ink/60 hover:text-ink text-2xl leading-none">×</button>
        </div>

        <form action="{{ route('admin.employees.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf

            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-ink/60 mb-1">Ảnh thẻ</label>
                <input type="file" name="photo" accept="image/*" class="w-full text-sm">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-ink/60 mb-1">Họ và tên</label>
                <input type="text" name="name" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Nguyễn Văn A">
            </div>
            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Số CCCD</label>
                <input type="text" name="national_id" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>

            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Ngày sinh</label>
                <input type="date" name="date_of_birth" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Số điện thoại</label>
                <input type="text" name="phone" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Email</label>
                <input type="email" name="email" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-ink/60 mb-1">Địa chỉ</label>
                <input type="text" name="address" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>

            <div class="md:col-span-3 border-t border-line pt-4 mt-1">
                <p class="text-xs font-medium text-ink/60 uppercase tracking-wide mb-3">Thông tin công việc</p>
            </div>

            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Phòng ban</label>
                <select name="department_id" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="">-- Chọn phòng ban --</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Chức vụ</label>
                <select name="position_id" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="">-- Chọn chức vụ --</option>
                    @foreach ($positions as $position)
                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Ngày vào công ty</label>
                <input type="date" name="hire_date" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>

            <div>
                <label class="block text-xs font-medium text-ink/60 mb-1">Loại hợp đồng</label>
                <select name="contract_type" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="probation">Thử việc</option>
                    <option value="official">Chính thức</option>
                    <option value="seasonal">Thời vụ</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-ink/60 mb-1">Lương cơ bản</label>
                <input type="number" name="base_salary" min="0" step="1000" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="5000000">
            </div>

            <div class="md:col-span-3 flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('createEmployeeModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm font-medium border border-line text-ink/70 hover:bg-canvas">
                    Hủy
                </button>
                <button type="submit" class="bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand/90">
                    Thêm nhân viên
                </button>
            </div>
        </form>
    </div>
</div>
@endsection