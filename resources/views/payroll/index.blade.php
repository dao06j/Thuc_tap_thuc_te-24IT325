@extends('layouts.app')

@section('title', 'Bảng lương của tôi')
@section('page-title', 'Bảng lương của tôi')
@section('page-subtitle', 'Tra cứu lương thực nhận theo từng tháng')

@section('content')
<div class="bg-white rounded-2xl border border-line overflow-hidden">
    <div class="p-5 border-b border-line">
        <h2 class="font-display font-semibold text-base">Lịch sử bảng lương</h2>
    </div>

    @if (! $hasEmployeeProfile)
        <div class="p-8 text-center text-sm text-ink/50">Tài khoản của bạn chưa được gắn với hồ sơ nhân viên.</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-ink/40 border-b border-line">
                        <th class="px-5 py-3 font-medium">Mã bảng lương</th>
                        <th class="px-5 py-3 font-medium">Tháng</th>
                        <th class="px-5 py-3 font-medium">Ngày công</th>
                        <th class="px-5 py-3 font-medium">Nghỉ có lương</th>
                        <th class="px-5 py-3 font-medium">Nghỉ không lương</th>
                        <th class="px-5 py-3 font-medium text-right">Thực nhận</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($payrolls as $payroll)
                        <tr class="hover:bg-canvas/60">
                            <td class="px-5 py-3 font-mono">{{ $payroll->code }}</td>
                            <td class="px-5 py-3 font-medium">{{ \Illuminate\Support\Carbon::createFromFormat('!Y-m', $payroll->month)->format('m/Y') }}</td>
                            <td class="px-5 py-3">{{ $payroll->work_days }}</td>
                            <td class="px-5 py-3">{{ $payroll->paid_leave_days }}</td>
                            <td class="px-5 py-3">{{ $payroll->unpaid_leave_days }}</td>
                            <td class="px-5 py-3 text-right font-semibold">{{ number_format((float) $payroll->salary_received, 0, ',', '.') }} ₫</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-ink/40">Chưa có bảng lương nào được ghi nhận.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payrolls->hasPages())
            <div class="p-4 border-t border-line">{{ $payrolls->links() }}</div>
        @endif
    @endif
</div>
@endsection
