@extends('layouts.app')

@section('title', 'Lịch sử chấm công')
@section('page-title', 'Lịch sử chấm công')
@section('page-subtitle', 'Bản ghi check-in/check-out kèm bằng chứng vị trí và ảnh')

@section('content')
<div class="bg-white rounded-2xl border border-line overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-ink/40 border-b border-line">
                <th class="px-5 py-3 font-medium">Ngày</th>
                <th class="px-5 py-3 font-medium">Check-in</th>
                <th class="px-5 py-3 font-medium">Check-out</th>
                <th class="px-5 py-3 font-medium">Khoảng cách</th>
                <th class="px-5 py-3 font-medium">Ảnh vào</th>
                <th class="px-5 py-3 font-medium">Ảnh ra</th>
                <th class="px-5 py-3 font-medium">Trạng thái</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @forelse ($records as $r)
                @php
                    if ($r->check_out) {
                        $label = $r->note ?: 'Đúng giờ';
                        $badge = $r->note ? 'bg-warn-light text-warn' : 'bg-accent-light text-accent';
                    } elseif ($r->check_in) {
                        $label = 'Chưa check-out';
                        $badge = 'bg-warn-light text-warn';
                    } else {
                        $label = '—';
                        $badge = 'bg-canvas text-ink/40';
                    }
                @endphp
                <tr class="hover:bg-canvas/60">
                    <td class="px-5 py-3 font-medium">{{ $r->work_date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 font-mono">{{ $r->check_in?->format('H:i:s') ?? '—' }}</td>
                    <td class="px-5 py-3 font-mono">{{ $r->check_out?->format('H:i:s') ?? '—' }}</td>
                    <td class="px-5 py-3 text-ink/60">{{ $r->distance_meters !== null ? $r->distance_meters . 'm' : '—' }}</td>
                    <td class="px-5 py-3">
                        @if ($r->proof_photo_path)
                            <a href="{{ asset('storage/' . $r->proof_photo_path) }}" target="_blank" class="block w-8 h-8 rounded-md overflow-hidden bg-slate-925">
                                <img src="{{ asset('storage/' . $r->proof_photo_path) }}" alt="" class="w-full h-full object-cover">
                            </a>
                        @else
                            <span class="text-ink/30">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if ($r->checkout_photo_path)
                            <a href="{{ asset('storage/' . $r->checkout_photo_path) }}" target="_blank" class="block w-8 h-8 rounded-md overflow-hidden bg-slate-925">
                                <img src="{{ asset('storage/' . $r->checkout_photo_path) }}" alt="" class="w-full h-full object-cover">
                            </a>
                        @else
                            <span class="text-ink/30">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $badge }}">{{ $label }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-ink/40">Chưa có dữ liệu chấm công.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection