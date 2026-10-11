@extends('layouts.app')

@section('title', 'Tổng quan')
@section('page-title', 'Chào buổi sáng, ' . ($employee['name'] ?? 'bạn'))
@section('page-subtitle', 'Hôm nay là ' . now()->translatedFormat('l, d/m/Y'))

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ==== SIGNATURE: CHECK-IN CARD ==== --}}
    <div class="lg:col-span-2 bg-slate-925 rounded-2xl p-7 text-white relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-brand/25 blur-3xl pointer-events-none"></div>

        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-slate-400 text-xs uppercase tracking-wider font-medium">Chấm công hôm nay</p>
                <p id="liveClock" class="font-mono text-5xl font-medium mt-2 tabular-nums">--:--:--</p>
                <p id="liveDate" class="text-slate-400 text-sm mt-1"></p>
            </div>
            <div class="text-right">
                @if ($todayAttendance?->check_out)
                    <span id="statusChip" class="inline-flex items-center gap-1.5 bg-accent-light text-accent text-xs font-medium px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-accent"></span> Đã hoàn tất hôm nay
                    </span>
                @elseif ($todayAttendance?->check_in)
                    <span id="statusChip" class="inline-flex items-center gap-1.5 bg-accent-light text-accent text-xs font-medium px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-accent"></span> Đã check-in {{ $todayAttendance->check_in->format('H:i') }}
                    </span>
                @else
                    <span id="statusChip" class="inline-flex items-center gap-1.5 bg-warn-light text-warn text-xs font-medium px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-warn"></span> Chưa check-in
                    </span>
                @endif
            </div>
        </div>

        <div class="relative grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
            {{-- Camera preview — luôn mở sẵn khi còn thao tác trong ngày --}}
            <div class="bg-slate-850 rounded-xl aspect-video flex items-center justify-center border border-slate-750 overflow-hidden relative">
                <video id="camPreview" autoplay muted playsinline class="w-full h-full object-cover hidden"></video>
                <canvas id="camCanvas" class="hidden"></canvas>
                <div id="camPlaceholder" class="text-center text-slate-500 px-4">
                    <svg class="w-8 h-8 mx-auto mb-2 animate-pulse" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <p class="text-xs">Đang mở camera…</p>
                    <button type="button" id="btnRetryCam" onclick="startCamera()" class="hidden mt-2 text-xs underline text-slate-300 hover:text-white">Thử mở lại camera</button>
                </div>
            </div>

            {{-- Location status --}}
            <div class="bg-slate-850 rounded-xl border border-slate-750 p-4 flex flex-col justify-between">
                <div>
                    <p class="text-xs text-slate-400 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="2.5"/></svg>
                        Vị trí hiện tại
                    </p>
                    <p id="locStatus" class="text-sm mt-2 text-slate-300">Đang lấy vị trí…</p>
                </div>
                <div class="mt-3">
                    <div class="flex justify-between text-xs text-slate-400 mb-1">
                        <span>Khoảng cách tới văn phòng</span>
                        <span id="distVal" class="font-mono">— m</span>
                    </div>
                    <div class="w-full bg-slate-750 rounded-full h-1.5">
                        <div id="distBar" class="bg-accent h-1.5 rounded-full" style="width:0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative flex gap-3 mt-6">
            <button onclick="doCheckIn()" id="btnCheckIn"
                    {{ $todayAttendance?->check_in ? 'disabled' : '' }}
                    class="flex-1 {{ $todayAttendance?->check_in ? 'bg-slate-750 text-slate-400 cursor-not-allowed' : 'bg-accent hover:bg-accent/90 text-white' }} font-medium text-sm py-3 rounded-xl transition-colors flex items-center justify-center gap-2">
                @unless ($todayAttendance?->check_in)
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-60"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                    </span>
                @endunless
                Check-in ngay
            </button>
            <button onclick="doCheckOut()" id="btnCheckOut"
                    {{ (!$todayAttendance?->check_in || $todayAttendance?->check_out) ? 'disabled' : '' }}
                    class="flex-1 {{ ($todayAttendance?->check_in && !$todayAttendance?->check_out) ? 'bg-white/10 text-white hover:bg-white/20' : 'bg-slate-750 text-slate-400 cursor-not-allowed' }} font-medium text-sm py-3 rounded-xl transition-colors">
                Check-out
            </button>
        </div>
        <p id="captureError" class="relative text-danger text-xs mt-3 hidden"></p>
        <p id="captureSuccess" class="relative text-accent text-xs mt-3 hidden"></p>
    </div>

    {{-- ==== SIDE STATS ==== --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-line p-5">
            <p class="text-xs text-ink/50 uppercase tracking-wide font-medium">Công tháng này</p>
            <p class="font-display text-3xl font-semibold mt-1">{{ $monthAttendanceCount }}<span class="text-base text-ink/40 font-body"> ngày</span></p>
            <div class="w-full bg-canvas rounded-full h-1.5 mt-3">
                <div class="bg-brand h-1.5 rounded-full" style="width: {{ min(100, round($monthAttendanceCount / 22 * 100)) }}%"></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-line p-5">
            <p class="text-xs text-ink/50 uppercase tracking-wide font-medium">Phép còn lại</p>
            <p class="font-display text-3xl font-semibold mt-1">—<span class="text-base text-ink/40 font-body"> ngày</span></p>
            <a href="{{ url('/leave') }}" class="text-xs text-brand font-medium mt-3 inline-block hover:underline">Gửi đơn nghỉ phép →</a>
        </div>
        <div class="bg-white rounded-2xl border border-line p-5">
            <p class="text-xs text-ink/50 uppercase tracking-wide font-medium mb-3">Chấm công gần đây</p>
            @if ($recentAttendances->isEmpty())
                <p class="text-sm text-ink/40">Chưa có dữ liệu.</p>
            @else
                <ul class="space-y-2.5 text-sm">
                    @foreach ($recentAttendances as $r)
                        <li class="flex justify-between">
                            <span class="text-ink/60">{{ $r->work_date->format('d/m') }}</span>
                            <span class="font-mono {{ $r->note ? 'text-warn' : 'text-ink' }}">
                                {{ $r->check_in?->format('H:i') ?? '--:--' }} – {{ $r->check_out?->format('H:i') ?? '--:--' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

<script>
    function tick() {
        const now = new Date();
        document.getElementById('liveClock').textContent = now.toLocaleTimeString('vi-VN', {hour12:false});
        document.getElementById('liveDate').textContent = now.toLocaleDateString('vi-VN', {weekday:'long', day:'2-digit', month:'2-digit', year:'numeric'});
    }
    setInterval(tick, 1000); tick();

    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    const OFFICE_LAT = {{ $officeLat }};
    const OFFICE_LNG = {{ $officeLng }};
    const ALREADY_CHECKED_IN = {{ $todayAttendance?->check_in ? 'true' : 'false' }};
    const ALREADY_CHECKED_OUT = {{ $todayAttendance?->check_out ? 'true' : 'false' }};

    let stream = null;
    let currentPosition = null;
    let locationTimer = null;

    function showError(msg) {
        const el = document.getElementById('captureError');
        el.textContent = msg;
        el.classList.remove('hidden');
        document.getElementById('captureSuccess').classList.add('hidden');
    }
    function showSuccess(msg) {
        const el = document.getElementById('captureSuccess');
        el.textContent = msg;
        el.classList.remove('hidden');
        document.getElementById('captureError').classList.add('hidden');
    }

    function haversine(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLon/2)**2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    function refreshLocation() {
        if (!navigator.geolocation) {
            document.getElementById('locStatus').textContent = 'Trình duyệt không hỗ trợ định vị';
            return;
        }
        navigator.geolocation.getCurrentPosition(pos => {
            const { latitude, longitude } = pos.coords;
            currentPosition = { latitude, longitude };
            document.getElementById('locStatus').textContent = `${latitude.toFixed(5)}, ${longitude.toFixed(5)}`;
            const dist = Math.round(haversine(latitude, longitude, OFFICE_LAT, OFFICE_LNG));
            document.getElementById('distVal').textContent = dist + ' m';
            document.getElementById('distBar').style.width = Math.min(100, (dist/500)*100) + '%';
        }, () => {
            document.getElementById('locStatus').textContent = 'Không lấy được vị trí — vui lòng cấp quyền';
            showError('Không thể truy cập vị trí. Kiểm tra quyền định vị của trình duyệt.');
        });
    }

    // Camera MỞ SẴN liên tục (không đợi bấm nút) — Check-in/Check-out chỉ việc chụp từ luồng đang chạy
    async function startCamera() {
        document.getElementById('btnRetryCam').classList.add('hidden');
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
            const video = document.getElementById('camPreview');
            video.srcObject = stream;
            video.classList.remove('hidden');
            document.getElementById('camPlaceholder').classList.add('hidden');
            return true;
        } catch (e) {
            document.getElementById('camPlaceholder').querySelector('p').textContent = 'Không mở được camera';
            document.getElementById('btnRetryCam').classList.remove('hidden');
            showError('Không thể truy cập camera. Kiểm tra quyền camera (yêu cầu HTTPS hoặc localhost).');
            return false;
        }
    }

    function capturePhoto() {
        return new Promise((resolve) => {
            const video = document.getElementById('camPreview');
            const canvas = document.getElementById('camCanvas');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            canvas.toBlob(blob => resolve(blob), 'image/jpeg', 0.85);
        });
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(t => t.stop());
            stream = null;
        }
        if (locationTimer) clearInterval(locationTimer);
        document.getElementById('camPreview').classList.add('hidden');
        document.getElementById('camPlaceholder').classList.remove('hidden');
        document.getElementById('camPlaceholder').querySelector('p').textContent = 'Đã hoàn tất chấm công hôm nay';
    }

    async function doCheckIn() {
        document.getElementById('captureError').classList.add('hidden');
        document.getElementById('captureSuccess').classList.add('hidden');

        const btn = document.getElementById('btnCheckIn');
        btn.disabled = true;

        if (!stream) {
            const ok = await startCamera();
            if (!ok) { btn.disabled = false; return; }
            await new Promise(r => setTimeout(r, 400));
        }
        if (!currentPosition) refreshLocation();

        const blob = await capturePhoto();

        if (!currentPosition) {
            showError('Cần có vị trí để check-in. Vui lòng cấp quyền định vị và thử lại.');
            btn.disabled = false;
            return;
        }

        const formData = new FormData();
        formData.append('photo', blob, 'checkin.jpg');
        formData.append('latitude', currentPosition.latitude);
        formData.append('longitude', currentPosition.longitude);

        try {
            const res = await fetch('{{ route('attendance.check-in') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json();

            if (!res.ok) {
                showError(data.message || 'Check-in thất bại.');
                btn.disabled = false;
                return;
            }

            showSuccess(data.message + (data.note ? ' — ' + data.note : ''));

            const chip = document.getElementById('statusChip');
            chip.className = 'inline-flex items-center gap-1.5 bg-accent-light text-accent text-xs font-medium px-2.5 py-1 rounded-full';
            chip.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-accent"></span> Đã check-in ' + data.check_in.slice(0,5);

            const btnOut = document.getElementById('btnCheckOut');
            btnOut.disabled = false;
            btnOut.className = 'flex-1 bg-white/10 text-white hover:bg-white/20 font-medium text-sm py-3 rounded-xl transition-colors';
            // Camera vẫn giữ mở vì còn cần chụp khi check-out
        } catch (e) {
            showError('Có lỗi xảy ra, vui lòng thử lại.');
            btn.disabled = false;
        }
    }

    async function doCheckOut() {
        document.getElementById('captureError').classList.add('hidden');
        document.getElementById('captureSuccess').classList.add('hidden');

        const btn = document.getElementById('btnCheckOut');
        btn.disabled = true;

        if (!stream) {
            const ok = await startCamera();
            if (!ok) { btn.disabled = false; return; }
            await new Promise(r => setTimeout(r, 400));
        }

        const blob = await capturePhoto();

        const formData = new FormData();
        formData.append('photo', blob, 'checkout.jpg');

        try {
            const res = await fetch('{{ route('attendance.check-out') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json();

            if (!res.ok) {
                showError(data.message || 'Check-out thất bại.');
                btn.disabled = false;
                return;
            }

            showSuccess(data.message + (data.note ? ' — ' + data.note : ''));

            const chip = document.getElementById('statusChip');
            chip.className = 'inline-flex items-center gap-1.5 bg-accent-light text-accent text-xs font-medium px-2.5 py-1 rounded-full';
            chip.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-accent"></span> Đã hoàn tất hôm nay';

            btn.className = 'flex-1 bg-slate-750 text-slate-400 cursor-not-allowed font-medium text-sm py-3 rounded-xl transition-colors';
            stopCamera();
        } catch (e) {
            showError('Có lỗi xảy ra, vui lòng thử lại.');
            btn.disabled = false;
        }
    }

    // Khởi động ngay khi vào trang: mở camera sẵn + lấy vị trí, nếu còn việc phải làm hôm nay
    (function init() {
        if (ALREADY_CHECKED_IN && ALREADY_CHECKED_OUT) {
            document.getElementById('camPlaceholder').querySelector('p').textContent = 'Đã hoàn tất chấm công hôm nay';
            return;
        }
        startCamera();
        refreshLocation();
        locationTimer = setInterval(refreshLocation, 30000);
    })();
</script>
@endsection