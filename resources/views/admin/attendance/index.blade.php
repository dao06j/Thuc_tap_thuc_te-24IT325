@extends('layouts.app')

@section('title', 'Quản lý chấm công')
@section('page-title', 'Quản lý chấm công')
@section('page-subtitle', 'Xem chấm công của toàn bộ nhân viên')

@section('content')
<div class="bg-white rounded-2xl border border-line overflow-hidden">
    <div class="flex flex-wrap items-center gap-3 p-5 border-b border-line">
        <form action="{{ route('admin.attendance') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Tìm theo tên nhân viên…"
                   class="border border-line rounded-lg text-sm px-3 py-2 w-56 focus:outline-none focus:ring-2 focus:ring-brand">

            <input type="date" name="date" value="{{ $date }}"
                   class="border border-line rounded-lg text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">

            <button type="submit" name="mode" value="date"
                    class="text-sm font-medium px-3 py-2 rounded-lg {{ $mode === 'date' ? 'bg-brand text-white' : 'border border-line text-ink/70 hover:bg-canvas' }}">
                Tìm theo ngày
            </button>
            <button type="submit" name="mode" value="name"
                    class="text-sm font-medium px-3 py-2 rounded-lg {{ $mode === 'name' ? 'bg-brand text-white' : 'border border-line text-ink/70 hover:bg-canvas' }}">
                Tìm theo tên
            </button>

            @if ($search !== '' || $date !== '')
                <a href="{{ route('admin.attendance') }}" class="text-xs text-ink/40 hover:text-ink/70 underline ml-1">Xóa lọc</a>
            @endif
        </form>
        <p class="text-xs text-ink/40 ml-auto">
            "Tìm theo ngày" áp dụng cả tên (nếu có nhập) · "Tìm theo tên" bỏ qua ngày
        </p>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-ink/40 border-b border-line">
                <th class="px-5 py-3 font-medium">Nhân viên</th>
                <th class="px-5 py-3 font-medium">Ngày</th>
                <th class="px-5 py-3 font-medium">Check-in</th>
                <th class="px-5 py-3 font-medium">Check-out</th>
                <th class="px-5 py-3 font-medium">Khoảng cách</th>
                <th class="px-5 py-3 font-medium">Ảnh vào/ra</th>
                <th class="px-5 py-3 font-medium">Ghi chú</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @forelse ($records as $r)
                <tr class="hover:bg-canvas/60">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-slate-925 text-white flex items-center justify-center text-xs font-display font-semibold overflow-hidden shrink-0">
                                @if ($r->employee?->photo_path)
                                    <img src="{{ asset('storage/' . $r->employee->photo_path) }}" alt="" class="w-full h-full object-cover">
                                @else
                                    {{ mb_substr($r->employee?->name ?? '?', 0, 1) }}
                                @endif
                            </div>
                            <div>
                                <p class="font-medium">{{ $r->employee?->name ?? '—' }}</p>
                                <p class="text-xs text-ink/40 font-mono">{{ $r->employee?->code }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 font-medium">{{ $r->work_date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 font-mono">{{ $r->check_in?->format('H:i:s') ?? '—' }}</td>
                    <td class="px-5 py-3 font-mono">{{ $r->check_out?->format('H:i:s') ?? '—' }}</td>
                    <td class="px-5 py-3 text-ink/60">{{ $r->distance_meters !== null ? $r->distance_meters . 'm' : '—' }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1.5">
                            @if ($r->proof_photo_path)
                                <a href="{{ asset('storage/' . $r->proof_photo_path) }}" target="_blank" class="block w-8 h-8 rounded-md overflow-hidden bg-slate-925" title="Ảnh check-in">
                                    <img src="{{ asset('storage/' . $r->proof_photo_path) }}" alt="" class="w-full h-full object-cover">
                                </a>
                            @else
                                <span class="w-8 h-8 rounded-md bg-canvas flex items-center justify-center text-ink/20 text-xs">—</span>
                            @endif
                            @if ($r->checkout_photo_path)
                                <a href="{{ asset('storage/' . $r->checkout_photo_path) }}" target="_blank" class="block w-8 h-8 rounded-md overflow-hidden bg-slate-925" title="Ảnh check-out">
                                    <img src="{{ asset('storage/' . $r->checkout_photo_path) }}" alt="" class="w-full h-full object-cover">
                                </a>
                            @else
                                <span class="w-8 h-8 rounded-md bg-canvas flex items-center justify-center text-ink/20 text-xs">—</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        @if ($r->note)
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-warn-light text-warn">{{ $r->note }}</span>
                        @else
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-accent-light text-accent">Đúng giờ</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-ink/40">Không tìm thấy bản ghi chấm công nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($records->hasPages())
        <div class="p-4 border-t border-line">
            {{ $records->links() }}
        </div>
    @endif
</div>
@endsection