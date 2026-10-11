@extends('layouts.app')

@section('title', 'Quản lý lương')
@section('page-title', 'Quản lý lương')
@section('page-subtitle', 'Tạo và cập nhật bảng lương theo tháng')

@section('content')
<div class="space-y-4">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-line p-5">
        <h2 class="font-display font-semibold text-base mb-4">Tạo hoặc cập nhật bảng lương</h2>
        <form action="{{ route('admin.payrolls.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @csrf
            <div>
                <label for="employee_id" class="block text-sm font-medium mb-1.5">Nhân viên</label>
                <select id="employee_id" name="employee_id" required class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    <option value="">Chọn nhân viên</option>
                    @foreach ($employees as $employeeOption)
                        <option value="{{ $employeeOption->id }}" @selected((string) old('employee_id') === (string) $employeeOption->id)>
                            {{ $employeeOption->name }} ({{ $employeeOption->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="month" class="block text-sm font-medium mb-1.5">Tháng lương</label>
                <input id="month" type="month" name="month" value="{{ old('month', $month) }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label for="salary_received" class="block text-sm font-medium mb-1.5">Lương thực nhận (₫)</label>
                <input id="salary_received" type="number" name="salary_received" value="{{ old('salary_received') }}" min="0" step="1000" required class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label for="work_days" class="block text-sm font-medium mb-1.5">Ngày công</label>
                <input id="work_days" type="number" name="work_days" value="{{ old('work_days', 0) }}" min="0" max="31" step="0.5" required class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label for="paid_leave_days" class="block text-sm font-medium mb-1.5">Ngày nghỉ có lương</label>
                <input id="paid_leave_days" type="number" name="paid_leave_days" value="{{ old('paid_leave_days', 0) }}" min="0" max="31" step="0.5" required class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label for="unpaid_leave_days" class="block text-sm font-medium mb-1.5">Ngày nghỉ không lương</label>
                <input id="unpaid_leave_days" type="number" name="unpaid_leave_days" value="{{ old('unpaid_leave_days', 0) }}" min="0" max="31" step="0.5" required class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="md:col-span-2 xl:col-span-3 flex justify-end">
                <button type="submit" class="bg-brand hover:bg-brand/90 text-white text-sm font-medium px-4 py-2.5 rounded-lg">Lưu bảng lương</button>
            </div>
        </form>
        <p class="text-xs text-ink/40 mt-3">Nếu nhân viên đã có bảng lương trong tháng được chọn, lưu biểu mẫu sẽ cập nhật bảng hiện có.</p>
    </div>

    <div class="bg-white rounded-2xl border border-line overflow-hidden">
        <form action="{{ route('admin.payrolls') }}" method="GET" class="flex flex-wrap items-end gap-3 p-5 border-b border-line">
            <div>
                <label for="filter_month" class="block text-xs text-ink/50 mb-1">Tháng</label>
                <input id="filter_month" type="month" name="month" value="{{ $month }}" class="border border-line rounded-lg text-sm px-3 py-2">
            </div>
            <div>
                <label for="search" class="block text-xs text-ink/50 mb-1">Nhân viên</label>
                <input id="search" type="text" name="search" value="{{ $search }}" placeholder="Tên hoặc mã nhân viên" class="border border-line rounded-lg text-sm px-3 py-2 w-56">
            </div>
            <button type="submit" class="bg-brand text-white text-sm font-medium px-4 py-2 rounded-lg">Lọc bảng lương</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-ink/40 border-b border-line">
                        <th class="px-5 py-3 font-medium">Nhân viên</th>
                        <th class="px-5 py-3 font-medium">Phòng ban</th>
                        <th class="px-5 py-3 font-medium">Ngày công</th>
                        <th class="px-5 py-3 font-medium">Nghỉ có lương</th>
                        <th class="px-5 py-3 font-medium">Nghỉ không lương</th>
                        <th class="px-5 py-3 font-medium text-right">Thực nhận</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($payrolls as $payroll)
                        <tr class="hover:bg-canvas/60">
                            <td class="px-5 py-3">
                                <p class="font-medium">{{ $payroll->employee?->name ?? 'Nhân viên không xác định' }}</p>
                                <p class="text-xs text-ink/40 font-mono">{{ $payroll->employee?->code }} · {{ $payroll->code }}</p>
                            </td>
                            <td class="px-5 py-3 text-ink/60">{{ $payroll->employee?->department?->name ?? '—' }}</td>
                            <td class="px-5 py-3">{{ $payroll->work_days }}</td>
                            <td class="px-5 py-3">{{ $payroll->paid_leave_days }}</td>
                            <td class="px-5 py-3">{{ $payroll->unpaid_leave_days }}</td>
                            <td class="px-5 py-3 text-right font-semibold">{{ number_format((float) $payroll->salary_received, 0, ',', '.') }} ₫</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-ink/40">Tháng này chưa có bảng lương nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payrolls->hasPages())
            <div class="p-4 border-t border-line">{{ $payrolls->links() }}</div>
        @endif
    </div>
</div>
@endsection
