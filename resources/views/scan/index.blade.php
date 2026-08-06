@extends('layouts.main-layout.app-transaction')
@section('title', 'Scan Tiket')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Scan Tiket</h4>
            <div>
                <span class="text-muted me-2">Wahana Anda:</span>
                @forelse ($wahanas as $w)
                    <span class="badge bg-label-primary me-1">{{ $w->name }}</span>
                @empty
                    <span class="badge bg-label-danger">Belum ada wahana ditugaskan — hubungi admin</span>
                @endforelse
            </div>
        </div>
        <div class="text-end">
            <div class="text-muted small">{{ auth()->user()->name }}</div>
            <a href="{{ route('logout') }}" class="btn btn-sm btn-label-secondary">
                <i class="ti ti-logout ti-sm me-1"></i>Logout
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <label class="form-label fw-bold">Kode Tiket</label>
                    <div class="input-group input-group-lg mb-2">
                        <input type="text" id="ticketCodeInput" class="form-control" placeholder="Scan / paste / ketik kode tiket"
                            autocomplete="off" autofocus>
                        <button class="btn btn-primary" id="submitScanBtn"><i class="ti ti-scan"></i> Scan</button>
                    </div>
                    <small class="text-muted">Kursor otomatis fokus di sini — alat barcode fisik cukup ditembakkan,
                        kode akan otomatis terkirim.</small>

                    <hr>

                    <button class="btn btn-outline-primary w-100" id="toggleCameraBtn" type="button">
                        <i class="ti ti-camera"></i> Buka Kamera Scan
                    </button>
                    <div id="cameraWrapper" class="mt-3 d-none">
                        <div id="qrReader" style="width: 100%;"></div>
                    </div>

                    <div id="scanResult" class="mt-3"></div>
                </div>
            </div>

            @can('scan-unflag')
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <button class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#unflagModal">
                            <i class="ti ti-lock-open"></i> Unflag Tiket (Otorisasi)
                        </button>
                    </div>
                </div>
            @endcan
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Scan Terakhir Hari Ini</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group" id="recentScanList">
                        @forelse ($recentScans as $r)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $r->ticket_code }} — {{ $r->wahana->name ?? '-' }}</span>
                                <span class="text-muted small">{{ optional($r->used_at)->format('H:i:s') }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Belum ada tiket yang discan hari ini</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="unflagModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Unflag Tiket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Tiket</label>
                        <input type="text" id="unflagTicketCode" class="form-control" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">PIN Otorisasi (SPV PIN)</label>
                        <input type="password" id="unflagSpvCode" class="form-control" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan</label>
                        <textarea id="unflagReason" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="submitUnflagBtn">Unflag</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('vuexy/assets/vendor/libs/html5-qrcode/html5-qrcode.js') }}"></script>
    <script>
        const csrfToken = '{{ csrf_token() }}';
        const scanUrl = '{{ route('scan.scan') }}';
        const unflagUrl = '{{ route('scan.unflag') }}';

        const ticketCodeInput = document.getElementById('ticketCodeInput');
        const scanResult = document.getElementById('scanResult');
        const recentScanList = document.getElementById('recentScanList');

        function beep(success) {
            try {
                const ctx = new(window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = success ? 880 : 220;
                gain.gain.value = 0.15;
                osc.start();
                setTimeout(() => {
                    osc.stop();
                    ctx.close();
                }, 180);
            } catch (e) {}
        }

        function showResult(status, message) {
            const cls = status === 'success' ? 'alert-success' : 'alert-danger';
            scanResult.innerHTML = `<div class="alert ${cls} mb-0">${message}</div>`;
            beep(status === 'success');
        }

        function prependRecent(code, wahana, time) {
            const empty = recentScanList.querySelector('.text-muted');
            if (empty) empty.remove();
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = `<span>${code} — ${wahana}</span><span class="text-muted small">${time}</span>`;
            recentScanList.prepend(li);
        }

        let submitting = false;

        function submitScan() {
            const code = ticketCodeInput.value.trim();
            if (!code || submitting) return Promise.resolve();
            submitting = true;

            return fetch(scanUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ticket_code: code
                    })
                })
                .then(res => res.json().then(body => ({
                    ok: res.ok,
                    body
                })))
                .then(({
                    body
                }) => {
                    showResult(body.status, body.message);
                    if (body.status === 'success' && body.data) {
                        prependRecent(body.data.ticket_code, body.data.wahana, body.data.used_at.split(' ')[1] ?? '');
                    }
                })
                .catch(() => showResult('error', 'Gagal menghubungi server'))
                .finally(() => {
                    submitting = false;
                    ticketCodeInput.value = '';
                    ticketCodeInput.focus();
                });
        }

        document.getElementById('submitScanBtn').addEventListener('click', submitScan);
        ticketCodeInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitScan();
            }
        });
        // Keep focus on the input so a hardware scanner's keyboard-wedge input always lands here
        document.addEventListener('click', function(e) {
            if (!document.getElementById('cameraWrapper').contains(e.target) && !e.target.closest('.modal')) {
                ticketCodeInput.focus();
            }
        });
        ticketCodeInput.focus();

        // ── Camera scan ──────────────────────────────
        let html5QrCode = null;
        let cameraOpen = false;
        // The QR success callback fires on EVERY decoded frame (~10/detik)
        // while the same code is still in view — without this lock, one
        // ticket in front of the camera for even half a second gets
        // submitted many times before it can be pulled away.
        let scanLocked = false;
        const toggleCameraBtn = document.getElementById('toggleCameraBtn');
        const cameraWrapper = document.getElementById('cameraWrapper');

        toggleCameraBtn.addEventListener('click', function() {
            if (cameraOpen) {
                stopCamera();
                return;
            }

            if (typeof Html5Qrcode === 'undefined') {
                showResult('error', 'Library scanner (html5-qrcode.js) gagal dimuat. Cek koneksi/asset aplikasi.');
                return;
            }

            // getUserMedia (akses kamera) hanya diizinkan browser di secure
            // context (HTTPS atau localhost). Kalau app diakses lewat IP LAN
            // polos (http://192.168.x.x), navigator.mediaDevices tidak ada
            // sama sekali dan kamera tidak akan pernah bisa dibuka.
            if (!window.isSecureContext || !navigator.mediaDevices) {
                showResult('error',
                    'Kamera tidak bisa diakses: halaman ini dibuka lewat ' + location.protocol +
                    '//' + location.host + ', bukan HTTPS/localhost. Browser memblokir akses kamera di luar secure context. ' +
                    'Gunakan flag Chrome "Insecure origins treated as secure" untuk origin ini, atau akses lewat HTTPS.');
                return;
            }

            cameraWrapper.classList.remove('d-none');
            scanLocked = false;
            html5QrCode = new Html5Qrcode('qrReader');
            html5QrCode.start({
                    facingMode: 'environment'
                }, {
                    fps: 10,
                    qrbox: 250
                },
                (decodedText) => {
                    if (scanLocked) return; // sudah ada scan yang lagi diproses/ditampilkan, abaikan frame ini
                    scanLocked = true;

                    // Bekukan kamera sesaat supaya QR yang sama tidak langsung
                    // terdeteksi & terkirim ulang selagi belum sempat dijauhkan.
                    if (typeof html5QrCode.pause === 'function') {
                        try {
                            html5QrCode.pause(true);
                        } catch (e) {}
                    }

                    ticketCodeInput.value = decodedText;
                    submitScan().finally(() => {
                        setTimeout(() => {
                            scanLocked = false;
                            if (cameraOpen && html5QrCode && typeof html5QrCode.resume === 'function') {
                                try {
                                    html5QrCode.resume();
                                } catch (e) {}
                            }
                        }, 1200);
                    });
                },
                () => {}
            ).then(() => {
                cameraOpen = true;
                toggleCameraBtn.innerHTML = '<i class="ti ti-camera-off"></i> Tutup Kamera Scan';
            }).catch((err) => {
                console.error('Gagal membuka kamera:', err);
                showResult('error', 'Tidak bisa mengakses kamera: ' + (err?.message || err));
                cameraWrapper.classList.add('d-none');
            });
        });

        function stopCamera() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => html5QrCode.clear()).catch(() => {});
            }
            cameraOpen = false;
            scanLocked = false;
            cameraWrapper.classList.add('d-none');
            toggleCameraBtn.innerHTML = '<i class="ti ti-camera"></i> Buka Kamera Scan';
        }

        // ── Unflag ───────────────────────────────────
        const submitUnflagBtn = document.getElementById('submitUnflagBtn');
        if (submitUnflagBtn) {
            submitUnflagBtn.addEventListener('click', function() {
                const payload = {
                    ticket_code: document.getElementById('unflagTicketCode').value.trim(),
                    spv_code: document.getElementById('unflagSpvCode').value.trim(),
                    reason: document.getElementById('unflagReason').value.trim(),
                };
                fetch(unflagUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(body => {
                        showResult(body.status, body.message);
                        if (body.status === 'success') {
                            $('#unflagModal').modal('hide');
                            document.getElementById('unflagTicketCode').value = '';
                            document.getElementById('unflagSpvCode').value = '';
                            document.getElementById('unflagReason').value = '';
                        }
                    })
                    .catch(() => showResult('error', 'Gagal menghubungi server'));
            });
        }
    </script>
@endsection
