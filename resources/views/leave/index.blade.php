@extends('layouts.app')

@section('title', 'Đơn nghỉ phép')
@section('page-title', 'Đơn nghỉ phép')
@section('page-subtitle', 'Gửi đơn mới và theo dõi trạng thái duyệt')

@section('content')
@php
    $leaveTypes = [
        'annual' => 'Nghỉ phép năm',
        'sick' => 'Nghỉ ốm',
        'unpaid' => 'Nghỉ việc riêng (không lương)',
        'wfh' => 'Làm việc từ xa (WFH)',
    ];
    $statusLabels = [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ];
@endphp

<div class="space-y-4">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">{{ session('error') }}</div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-line p-6 h-fit">
            <h2 class="font-display font-semibold text-base mb-4">Gửi đơn mới</h2>
            @if ($hasEmployeeProfile)
                <form action="{{ route('leave.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="leave_type" class="block text-sm font-medium mb-1.5">Loại phép</label>
                        <select id="leave_type" name="leave_type" required class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                            @foreach ($leaveTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('leave_type', 'annual') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="from_date" class="block text-sm font-medium mb-1.5">Từ ngày</label>
                            <input id="from_date" type="date" name="from_date" value="{{ old('from_date') }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label for="to_date" class="block text-sm font-medium mb-1.5">Đến ngày</label>
                            <input id="to_date" type="date" name="to_date" value="{{ old('to_date') }}" required class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="reason" class="block text-sm font-medium mb-1.5">Lý do</label>
                        <textarea id="reason" name="reason" rows="3" maxlength="255" required placeholder="Mô tả ngắn gọn lý do xin nghỉ…" class="w-full border border-line rounded-lg px-3 py-2 text-sm resize-none">{{ old('reason') }}</textarea>
                    </div>
                    <button type="submit" class="w-full bg-brand hover:bg-brand/90 text-white text-sm font-medium py-2.5 rounded-lg">
                        Gửi đơn
                    </button>
                </form>
            @else
                <p class="text-sm text-ink/60">Tài khoản của bạn chưa được gắn với hồ sơ nhân viên nên chưa thể gửi đơn.</p>
            @endif
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl border border-line overflow-hidden h-fit">
            <div class="p-5 border-b border-line">
                <h2 class="font-display font-semibold text-base">Đơn đã gửi</h2>
            </div>
            <ul class="divide-y divide-line">
                @forelse ($leaveRequests as $leave)
                    @php
                        $badge = match ($leave->status) {
                            'approved' => 'bg-accent-light text-accent',
                            'rejected' => 'bg-danger-light text-danger',
                            default => 'bg-warn-light text-warn',
                        };
                    @endphp
                    <li class="p-5 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-medium text-sm">{{ $leaveTypes[$leave->leave_type] ?? $leave->leave_type }} <span class="text-ink/40 font-mono text-xs">· {{ $leave->code }}</span></p>
                            <p class="text-xs text-ink/50 mt-1 font-mono">{{ $leave->from_date->format('d/m/Y') }} – {{ $leave->to_date->format('d/m/Y') }} · {{ $leave->total_days }} ngày</p>
                            <p class="text-sm text-ink/70 mt-2">{{ $leave->reason }}</p>
                            @if ($leave->approver)
                                <p class="text-xs text-ink/40 mt-2">Người xử lý: {{ $leave->approver->name }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 text-xs font-medium px-2.5 py-1 rounded-full {{ $badge }}">{{ $statusLabels[$leave->status] ?? $leave->status }}</span>
                    </li>
                @empty
                    <li class="p-8 text-center text-sm text-ink/40">Bạn chưa gửi đơn nghỉ phép nào.</li>
                @endforelse
            </ul>
            @if ($leaveRequests instanceof \Illuminate\Contracts\Pagination\Paginator && $leaveRequests->hasPages())
                <div class="p-4 border-t border-line">{{ $leaveRequests->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
