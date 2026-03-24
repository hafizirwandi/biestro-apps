{{--
    Partial: Ticket Sold Modal
    Includes: modal HTML + AJAX JS + scrollbar style
    Usage: @include('transaction.partials._ticket-sold-modal')
    Requires: route('transaction.ticket-sold') available
--}}

<!-- Ticket Sold Modal -->
<div class="modal fade" id="modalTicketSold" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);color:#fff;">
                <h5 class="modal-title text-white"><i class="ti ti-chart-pie me-2"></i>Ticket Sold Dashboard</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light p-3">
                <!-- Loading -->
                <div id="tsLoading" class="text-center py-5">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="mt-2 text-muted">Memuat data tiket...</p>
                </div>

                <!-- Content -->
                <div id="tsContent" style="display:none;">
                    <p class="text-muted small text-uppercase fw-bold mb-2">
                        <i class="ti ti-trophy text-warning me-1"></i>Top 3 Terjual
                    </p>
                    <div class="row g-3 mb-4" id="tsTop3"></div>

                    <div class="card border-0 shadow-sm">
                        <div
                            class="card-header bg-white d-flex align-items-center justify-content-between border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="ti ti-list me-2 text-primary"></i>Semua Tiket Terjual
                            </h6>
                            <span class="badge bg-primary" id="tsTotalBadge">0 jenis</span>
                        </div>
                        <div style="max-height:320px;overflow-y:auto;">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>Nama Tiket / Paket</th>
                                        <th class="text-end pe-3">Qty Terjual</th>
                                    </tr>
                                </thead>
                                <tbody id="tsAllList"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openTicketSoldModal() {
        const modal = new bootstrap.Modal(document.getElementById('modalTicketSold'));
        modal.show();

        const loading = document.getElementById('tsLoading');
        const content = document.getElementById('tsContent');
        const top3El = document.getElementById('tsTop3');
        const allListEl = document.getElementById('tsAllList');
        const badge = document.getElementById('tsTotalBadge');

        // Reset
        content.style.display = 'none';
        loading.style.display = 'block';
        top3El.innerHTML = '';
        allListEl.innerHTML = '';

        fetch('{{ route('transaction.ticket-sold') }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                loading.style.display = 'none';
                content.style.display = 'block';

                // Top 3
                const colors = [
                    'linear-gradient(135deg,#f59e0b,#d97706)',
                    'linear-gradient(135deg,#6b7280,#4b5563)',
                    'linear-gradient(135deg,#92400e,#78350f)',
                ];
                const labels = ['🥇 #1', '🥈 #2', '🥉 #3'];

                if (data.top3 && data.top3.length > 0) {
                    data.top3.forEach((t, i) => {
                        top3El.innerHTML += `
                    <div class="col-md-4">
                        <div class="card text-white border-0 h-100" style="background:${colors[i]||'linear-gradient(135deg,#475569,#334155)'};">
                            <div class="card-body text-center p-3">
                                <div class="fs-3 fw-black mb-1">${t.qty}</div>
                                <div class="small fw-bold">${labels[i] || `#${i+1}`}</div>
                                <div class="small opacity-75 mt-1" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${t.name}">${t.name}</div>
                            </div>
                        </div>
                    </div>`;
                    });
                } else {
                    top3El.innerHTML =
                        '<div class="col-12 text-center text-muted py-3">Belum ada tiket terjual.</div>';
                }

                // All list
                if (data.all && data.all.length > 0) {
                    badge.textContent = data.all.length + ' jenis';
                    data.all.forEach((t, i) => {
                        allListEl.innerHTML += `
                    <tr>
                        <td class="ps-3 text-muted">${i+1}</td>
                        <td class="fw-medium">${t.name}</td>
                        <td class="text-end pe-3 fw-bold text-primary">${t.qty}</td>
                    </tr>`;
                    });
                } else {
                    badge.textContent = '0 jenis';
                    allListEl.innerHTML =
                        '<tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data.</td></tr>';
                }
            })
            .catch(() => {
                loading.innerHTML = '<div class="alert alert-danger">Gagal mengambil data. Coba lagi.</div>';
            });
    }
</script>
