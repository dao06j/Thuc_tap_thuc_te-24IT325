@extends('layouts.app')

@section('title', 'Duyệt đơn nghỉ phép')
@section('page-title', 'Duyệt đơn nghỉ phép')
@section('page-subtitle', 'Xử lý đơn nghỉ phép của nhân viên')

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
    $tabs = [
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

    <div class="bg-white rounded-2xl border border-line overflow-hidden">
        <div class="flex flex-wrap items-center gap-2 p-5 border-b border-line">
            @foreach ($tabs as $tabStatus => $tabLabel)
                <a href="{{ route('admin.leave-approvals', ['status' => $tabStatus]) }}"
                   class="text-sm font-medium px-3 py-1.5 rounded-full {{ $status === $tabStatus ? 'bg-brand text-white' : 'text-ink/50 hover:bg-canvas' }}">
                    {{ $tabLabel }} <span class="ml-1 opacity-80">{{ $statusCounts[$tabStatus] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <ul class="divide-y divide-line">
            @forelse ($leaveRequests as $leave)
                <li class="p-5 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="flex gap-3 min-w-0">
                        <div class="w-9 h-9 shrink-0 rounded-full bg-slate-925 text-white flex items-center justify-center text-sm font-display font-semibold">
                            {{ mb_substr($leave->employee?->name ?? '?', 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium text-sm">
                                {{ $leave->employee?->name ?? 'Nhân viên không xác định' }}
                                <span class="text-ink/40 font-normal">· {{ $leave->employee?->department?->name ?? 'Chưa phân phòng ban' }}</span>
                                <span class="text-ink/40 font-mono text-xs">· {{ $leave->code }}</span>
                            </p>
                            <p class="text-sm text-ink/70 mt-1">
                                {{ $leaveTypes[$leave->leave_type] ?? $leave->leave_type }} —
                                <span class="font-mono text-xs">{{ $leave->from_date->format('d/m/Y') }} – {{ $leave->to_date->format('d/m/Y') }} ({{ $leave->total_days }} ngày)</span>
                            </p>
                            <p class="text-sm text-ink/50 mt-1">{{ $leave->reason }}</p>
                            @if ($leave->approver)
                                <p class="text-xs text-ink/40 mt-2">Người xử lý: {{ $leave->approver->name }}</p>
                            @endif
                            @if ((int) $leave->employee_id === (int) $currentEmployeeId)
                                <p class="text-xs text-danger mt-2">Bạn không thể tự duyệt đơn nghỉ phép của chính mình.</p>
                            @endif
                        </div>
                    </div>

                    @if ($leave->status === 'pending' && (int) $leave->employee_id !== (int) $currentEmployeeId)
                        <div class="flex gap-2 shrink-0 sm:pt-1">
                            <form action="{{ route('admin.leave-approvals.update', [$leave, 'reject']) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs font-medium px-3 py-1.5 rounded-lg border border-line hover:bg-canvas">Từ chối</button>
                            </form>
                            <form action="{{ route('admin.leave-approvals.update', [$leave, 'approve']) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs font-medium px-3 py-1.5 rounded-lg bg-accent text-white hover:bg-accent/90">Duyệt</button>
                            </form>
                        </div>
                    @else
                        <span class="shrink-0 text-xs font-medium px-2.5 py-1 rounded-full {{ $leave->status === 'approved' ? 'bg-accent-light text-accent' : ($leave->status === 'rejected' ? 'bg-danger-light text-danger' : 'bg-warn-light text-warn') }}">
                            {{ $statusLabels[$leave->status] ?? $leave->status }}
                        </span>
                    @endif
                </li>
            @empty
                <li class="p-8 text-center text-sm text-ink/40">Không có đơn nghỉ phép ở trạng thái này.</li>
            @endforelse
        </ul>

        @if ($leaveRequests->hasPages())
            <div class="p-4 border-t border-line">{{ $leaveRequests->links() }}</div>
        @endif
    </div>
</div>
@endsection
