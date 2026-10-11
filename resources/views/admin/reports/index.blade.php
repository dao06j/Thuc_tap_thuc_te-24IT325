@extends('layouts.app')

@section('title', 'Thống kê & báo cáo')
@section('page-title', 'Thống kê & báo cáo')
@section('page-subtitle', 'Tổng quan nhân sự, chấm công, nghỉ phép và quỹ lương')

@push('head')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="space-y-6">
    <form action="{{ url('/admin/reports') }}" method="GET" class="flex flex-wrap items-end gap-3 bg-white rounded-2xl border border-line p-5">
        <div>
            <label for="month" class="block text-xs text-ink/50 mb-1">Tháng thống kê</label>
            <input id="month" type="month" name="month" value="{{ $month }}" class="border border-line rounded-lg text-sm px-3 py-2">
        </div>
        <button type="submit" class="bg-brand text-white text-sm font-medium px-4 py-2 rounded-lg">Xem báo cáo</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-line p-5">
            <p class="text-xs text-ink/50 uppercase tracking-wide font-medium">Nhân viên đang làm</p>
            <p class="font-display text-2xl font-semibold mt-1.5">{{ number_format($activeEmployeeCount) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-line p-5">
            <p class="text-xs text-ink/50 uppercase tracking-wide font-medium">Đã chấm công hôm nay</p>
            <p class="font-display text-2xl font-semibold mt-1.5">{{ number_format($todayAttendanceCount) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-line p-5">
            <p class="text-xs text-ink/50 uppercase tracking-wide font-medium">Đơn nghỉ chờ duyệt</p>
            <p class="font-display text-2xl font-semibold mt-1.5">{{ number_format($pendingLeaveCount) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-line p-5">
            <p class="text-xs text-ink/50 uppercase tracking-wide font-medium">Quỹ lương tháng {{ \Illuminate\Support\Carbon::createFromFormat('!Y-m', $month)->format('m/Y') }}</p>
            <p class="font-display text-2xl font-semibold mt-1.5">{{ number_format((float) $monthlyPayrollTotal, 0, ',', '.') }} ₫</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <section class="bg-white rounded-2xl border border-line p-5">
            <h2 class="font-display font-semibold text-base">Cơ cấu nhân viên theo phòng ban</h2>
            <p class="text-xs text-ink/50 mt-1">Chỉ tính nhân viên đang làm việc</p>
            <div class="relative h-80 mt-4">
                <canvas id="employeeChart" role="img" aria-label="Biểu đồ tròn cơ cấu nhân viên theo phòng ban"></canvas>
            </div>
        </section>
        <section class="bg-white rounded-2xl border border-line p-5">
            <h2 class="font-display font-semibold text-base">Tình hình chấm công tháng {{ \Illuminate\Support\Carbon::createFromFormat('!Y-m', $month)->format('m/Y') }}</h2>
            <p class="text-xs text-ink/50 mt-1">Đúng giờ: {{ $onTimeRate }}% trên tổng lượt check-in</p>
            <div class="relative h-80 mt-4">
                <canvas id="attendanceChart" role="img" aria-label="Biểu đồ cột lượt chấm công đúng giờ và đi trễ mỗi ngày"></canvas>
            </div>
        </section>
    </div>

    <section class="bg-white rounded-2xl border border-line overflow-hidden">
        <div class="p-5 border-b border-line">
            <h2 class="font-display font-semibold text-base">Tổng hợp theo phòng ban</h2>
            <p class="text-xs text-ink/50 mt-1">Dữ liệu chấm công, nghỉ phép đã duyệt và lương trong tháng được chọn</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-ink/40 border-b border-line">
                        <th class="px-5 py-3 font-medium">Phòng ban</th>
                        <th class="px-5 py-3 font-medium text-right">Nhân viên</th>
                        <th class="px-5 py-3 font-medium text-right">Lượt chấm công</th>
                        <th class="px-5 py-3 font-medium text-right">Lượt đi trễ</th>
                        <th class="px-5 py-3 font-medium text-right">Ngày nghỉ đã duyệt</th>
                        <th class="px-5 py-3 font-medium text-right">Quỹ lương</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($departmentStats as $department)
                        <tr class="hover:bg-canvas/60">
                            <td class="px-5 py-3 font-medium">{{ $department['name'] }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($department['employees']) }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($department['attendance']) }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($department['late']) }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($department['leave_days'], 1) }}</td>
                            <td class="px-5 py-3 text-right font-medium">{{ number_format($department['payroll'], 0, ',', '.') }} ₫</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-ink/40">Chưa có dữ liệu phòng ban.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($departmentStats->isNotEmpty())
                    <tfoot class="bg-canvas/60 font-semibold">
                        <tr>
                            <td class="px-5 py-3">Tổng cộng</td>
                            <td class="px-5 py-3 text-right">{{ number_format($departmentStats->sum('employees')) }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($departmentStats->sum('attendance')) }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($departmentStats->sum('late')) }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($departmentStats->sum('leave_days'), 1) }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($departmentStats->sum('payroll'), 0, ',', '.') }} ₫</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </section>
</div>

<script>
    const reportColors = ['#4338CA', '#0D9488', '#B45309', '#B91C1C', '#2563EB', '#9333EA', '#64748B'];
    const employeeChartData = @json($employeeChart);
    const attendanceChartData = @json($attendanceChart);

    new Chart(document.getElementById('employeeChart'), {
        type: 'pie',
        data: {
            labels: employeeChartData.labels,
            datasets: [{
                data: employeeChartData.data,
                backgroundColor: reportColors,
                borderColor: '#FFFFFF',
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } },
            },
        },
    });

    new Chart(document.getElementById('attendanceChart'), {
        type: 'bar',
        data: {
            labels: attendanceChartData.labels,
            datasets: [{
                label: 'Đúng giờ',
                data: attendanceChartData.onTime,
                backgroundColor: '#0D9488',
                borderRadius: 4,
            }, {
                label: 'Đi trễ',
                data: attendanceChartData.late,
                backgroundColor: '#B45309',
                borderRadius: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } },
            },
            scales: {
                x: { stacked: true, grid: { display: false }, title: { display: true, text: 'Ngày trong tháng' } },
                y: { stacked: true, beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Lượt check-in' } },
            },
        },
    });
</script>
@endsection
