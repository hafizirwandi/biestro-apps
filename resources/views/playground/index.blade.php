@extends('layouts.main-layout.app-transaction')
@section('title', 'Playground')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Playground</h4>
        <div class="text-end d-flex align-items-center gap-2">
            @can('playground-report')
                <a href="{{ route('playground.today-report') }}" target="_blank" rel="noopener"
                    class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-report ti-sm me-1"></i>Report Hari Ini
                </a>
            @endcan
            <div>
                <div class="text-muted small">{{ auth()->user()->name }}</div>
                <a href="{{ route('logout') }}" class="btn btn-sm btn-label-secondary">
                    <i class="ti ti-logout ti-sm me-1"></i>Logout
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Anak Main Hari Ini</h6>
                    <h4 class="fw-bold" id="statTotal">{{ $stat['total'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Sedang Bermain</h6>
                    <h4 class="fw-bold text-primary" id="statOngoing">{{ $stat['ongoing'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Menunggu Dijemput</h6>
                    <h4 class="fw-bold text-danger" id="statTimeUp">{{ $stat['time_up'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Sudah Selesai</h6>
                    <h4 class="fw-bold text-success" id="statPickedUp">{{ $stat['picked_up'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    @can('playground-input')
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h6 class="mb-3">Tambah Anak</h6>
                <form id="playgroundForm" class="row g-3" method="post" action="{{ route('playground.store') }}">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">Nama Anak</label>
                        <input type="text" name="child_name" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="gender" class="form-control" required>
                            <option value="male">Laki-laki</option>
                            <option value="female">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Warna Baju</label>
                        <input type="text" name="clothing_color" class="form-control" placeholder="cth: Merah" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Lama Bermain (menit)</label>
                        <input type="number" name="duration_minutes" class="form-control" value="30" min="1" max="600"
                            required>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="ti ti-plus"></i></button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Anak Bermain Hari Ini</h6>
            <span id="callingIndicator" class="badge bg-danger d-none">
                <i class="ti ti-volume"></i> Sedang memanggil...
            </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="sessionsTable">
                    <thead>
                        <tr>
                            <th>Nama Anak</th>
                            <th>Jenis Kelamin</th>
                            <th>Warna Baju</th>
                            <th>Durasi</th>
                            <th>Sisa Waktu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="sessionsBody">
                        <tr>
                            <td colspan="7" class="text-muted text-center">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('playground-input')
        <div class="modal fade" id="editSessionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Data Anak</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editSessionId">
                        <div class="mb-3">
                            <label class="form-label">Nama Anak</label>
                            <input type="text" id="editChildName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select id="editGender" class="form-control" required>
                                <option value="male">Laki-laki</option>
                                <option value="female">Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Warna Baju</label>
                            <input type="text" id="editClothingColor" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lama Bermain (menit)</label>
                            <input type="number" id="editDurationMinutes" class="form-control" min="1" max="600"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="submitEditBtn">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

@section('script')
    <script>
        const activeSessionsUrl = '{{ route('playground.active-sessions') }}';
        const callManualUrlBase = '{{ url('/playground') }}';
        const csrfToken = '{{ csrf_token() }}';
        const canInput = @json(auth()->user()->can('playground-input'));
        let sessionsById = {};

        const sessionsBody = document.getElementById('sessionsBody');
        const callingIndicator = document.getElementById('callingIndicator');
        const statTotal = document.getElementById('statTotal');
        const statOngoing = document.getElementById('statOngoing');
        const statTimeUp = document.getElementById('statTimeUp');
        const statPickedUp = document.getElementById('statPickedUp');

        const callingLoops = {}; // sessionId -> intervalId

        function speak(text) {
            if (!('speechSynthesis' in window)) return;
            const utter = new SpeechSynthesisUtterance(text);
            utter.lang = 'id-ID';
            window.speechSynthesis.speak(utter);
        }

        function speakForSession(session) {
            speak(`Anak atas nama ${session.child_name}, waktunya sudah habis`);
        }

        function ensureCallingLoop(session) {
            if (callingLoops[session.id]) return;
            speakForSession(session);
            callingLoops[session.id] = setInterval(() => speakForSession(session), 8000);
        }

        function clearCallingLoop(id) {
            if (callingLoops[id]) {
                clearInterval(callingLoops[id]);
                delete callingLoops[id];
            }
        }

        function formatDuration(sec) {
            sec = Math.max(0, sec);
            const m = Math.floor(sec / 60).toString().padStart(2, '0');
            const s = Math.floor(sec % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        }

        function postAction(path) {
            return fetch(`${callManualUrlBase}/${path}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            }).then(() => loadSessions());
        }

        function sendAction(path, method, body) {
            return fetch(`${callManualUrlBase}/${path}`, {
                method,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: body ? JSON.stringify(body) : undefined
            }).then(() => loadSessions());
        }

        function openEditModal(id) {
            const s = sessionsById[id];
            if (!s) return;
            document.getElementById('editSessionId').value = s.id;
            document.getElementById('editChildName').value = s.child_name;
            document.getElementById('editGender').value = s.gender;
            document.getElementById('editClothingColor').value = s.clothing_color;
            document.getElementById('editDurationMinutes').value = s.duration_minutes;
            $('#editSessionModal').modal('show');
        }

        function deleteSession(id) {
            const s = sessionsById[id];
            Swal.fire({
                icon: 'warning',
                title: 'Hapus data anak?',
                text: `Data "${s ? s.child_name : ''}" akan dihapus permanen.`,
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) sendAction(id, 'DELETE');
            });
        }

        function loadSessions() {
            fetch(activeSessionsUrl, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(sessions => {
                    renderSessions(sessions);
                })
                .catch(() => {});
        }

        function renderSessions(sessions) {
            const activeIds = new Set(sessions.map(s => s.id));
            Object.keys(callingLoops).forEach(id => {
                if (!activeIds.has(Number(id))) clearCallingLoop(id);
            });

            let anyCalling = false;
            let ongoing = 0,
                timeUp = 0;

            sessionsById = {};

            if (sessions.length === 0) {
                sessionsBody.innerHTML = '<tr><td colspan="7" class="text-muted text-center">Belum ada anak bermain hari ini</td></tr>';
            } else {
                sessionsBody.innerHTML = sessions.map(s => {
                    sessionsById[s.id] = s;
                    if (s.status === 'ongoing') ongoing++;
                    if (s.status === 'time_up') timeUp++;

                    if (s.is_calling) {
                        anyCalling = true;
                        ensureCallingLoop(s);
                    } else {
                        clearCallingLoop(s.id);
                    }

                    const statusBadge = s.status === 'time_up' ?
                        '<span class="badge bg-danger">Waktu Habis</span>' :
                        '<span class="badge bg-primary">Bermain</span>';

                    const genderLabel = s.gender === 'male' ? 'Laki-laki' : 'Perempuan';

                    const callBtn = s.is_calling ?
                        `<button class="btn btn-sm btn-outline-secondary" onclick="postAction('${s.id}/stop-calling')">Hentikan Pemanggilan</button>` :
                        `<button class="btn btn-sm btn-outline-warning" onclick="postAction('${s.id}/call-manual')">Panggil Manual</button>`;

                    const manageBtns = canInput ? `
                            <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(${s.id})"><i class="ti ti-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSession(${s.id})"><i class="ti ti-trash"></i></button>` : '';

                    return `<tr class="${s.status === 'time_up' ? 'table-danger' : ''}">
                        <td>${s.child_name}</td>
                        <td>${genderLabel}</td>
                        <td>${s.clothing_color}</td>
                        <td>${s.duration_minutes} menit</td>
                        <td>${formatDuration(s.remaining_seconds)}</td>
                        <td>${statusBadge}</td>
                        <td class="d-flex gap-1 flex-wrap">
                            ${callBtn}
                            <button class="btn btn-sm btn-success" onclick="postAction('${s.id}/finish')">Sudah Diambil</button>
                            ${manageBtns}
                        </td>
                    </tr>`;
                }).join('');
            }

            callingIndicator.classList.toggle('d-none', !anyCalling);
            statOngoing.textContent = ongoing;
            statTimeUp.textContent = timeUp;
        }

        document.getElementById('playgroundForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(() => {
                    form.reset();
                    statTotal.textContent = (parseInt(statTotal.textContent) || 0) + 1;
                    loadSessions();
                });
        });

        document.getElementById('submitEditBtn')?.addEventListener('click', function() {
            const id = document.getElementById('editSessionId').value;
            sendAction(id, 'PUT', {
                child_name: document.getElementById('editChildName').value.trim(),
                gender: document.getElementById('editGender').value,
                clothing_color: document.getElementById('editClothingColor').value.trim(),
                duration_minutes: parseInt(document.getElementById('editDurationMinutes').value, 10),
            }).then(() => $('#editSessionModal').modal('hide'));
        });

        loadSessions();
        setInterval(loadSessions, 5000);
    </script>
@endsection
