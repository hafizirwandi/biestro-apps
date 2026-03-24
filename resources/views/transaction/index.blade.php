@extends('layouts.main-layout.app-transaction')
@section('title', 'Ticket')
@section('css')
    <style>
        .wahana-card {
            /* background-color: #fff; */
            height: 150px !important;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: 0.2s ease;
            cursor: pointer;
            position: relative;



        }



        .wahana-info {
            position: absolute;
            top: 5px;
            right: 5px;
        }

        .wahana-info:hover {
            /* color: blue; */

        }

        .wahana-card:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .wahana-number {
            font-weight: bold;
            font-size: 24px;
            /* color: #333; */
        }

        .wahana-name {
            font-size: 16px;
            /* color: #555; */
            font-weight: 500;
        }

        .title-order {
            font-size: 2rem;
            /* border-bottom: 1px solid #000 */
        }

        .cart-item {
            gap: 10px;
        }

        .cart-item>div {
            min-width: fit-content;
        }

        @media (max-width: 576px) {
            .cart-item {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .cart-item .text-end {
                align-self: flex-end;
            }
        }

        .draft-item {
            /* border: 1px solid #000; */
            padding: 10px;
            cursor: pointer;
            border-radius: 4px;
            min-width: 220px;
            max-width: 250px;
            flex: 1;
        }

        .draft-item:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .draft-item.active {
            border: 2px solid #7367f0 !important;
            /* background-color: #f0e6f9; */
        }



        .kp-voucher-main {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            border-radius: 0 15px 15px 0;
            box-shadow: 0 15px 35px rgba(107, 33, 168, 0.3);
            position: relative;
            width: 100%;
            /* height: 150px; */
            overflow: hidden;
            color: white;
            display: flex;
            align-items: center;
            padding-left: 40px;
            margin-top: 5px;

        }

        .kp-voucher-main::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 30px;
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            clip-path: polygon(0% 0%,
                    70% 0%,
                    100% 10%,
                    70% 20%,
                    100% 30%,
                    70% 40%,
                    100% 50%,
                    70% 60%,
                    100% 70%,
                    70% 80%,
                    100% 90%,
                    70% 100%,
                    0% 100%);
        }

        .kp-voucher-content {
            z-index: 2;
            position: relative;
            width: 100%;
            padding-left: 20px;
            padding-bottom: 10px;
            padding-top: 10px;
        }

        .kp-voucher-title {
            font-size: 20px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .kp-voucher-discount {
            font-size: 30px;
            font-weight: 900;
            color: #fbbf24;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 5px;
        }

        .kp-voucher-description {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 2px;
        }



        .kp-voucher-pattern {
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 2px, transparent 2px);
            background-size: 20px 20px;
            border-radius: 50%;
            opacity: 0.3;
        }

        .kp-voucher-list {
            margin: 0;
            padding: 0;
            opacity: 0.8;
            font-size: 12px;

        }

        /* ── Session Header ── */
        .pos-session-bar {
            background: linear-gradient(135deg, #1e1e2e 0%, #2d2d44 100%);
            color: #e0e0f0;
            border-radius: 10px;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 18px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
        }

        .pos-session-bar .session-info {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .pos-session-bar .session-item {
            display: flex;
            flex-direction: column;
            min-width: 80px;
        }

        .pos-session-bar .session-item .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #9e9ec0;
        }

        .pos-session-bar .session-item .value {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
        }

        .pos-session-bar .divider {
            width: 1px;
            height: 30px;
            background: rgba(255, 255, 255, 0.15);
        }

        .btn-printer {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #e0e0f0;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-printer:hover {
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .btn-printer.connected {
            border-color: #28c76f;
            color: #28c76f;
        }

        .printer-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #9e9ec0;
            transition: background 0.3s;
        }

        .printer-dot.connected {
            background: #28c76f;
            box-shadow: 0 0 6px #28c76f80;
        }
    </style>

@endsection
@section('content')

    {{-- ░░ Session Header Bar (component) ░░ --}}
    @include('transaction.partials._session-bar', ['shift' => $shift, 'pageContext' => 'index'])
    @include('transaction.partials._ticket-sold-modal')
    @include('transaction.partials._wahana-sold-modal')
    <div class="d-flex flex-wrap gap-3 mb-4" id="draftList"></div>

    <div class="row">
        <div class="col-md-8">
            <div style="height:55vh; overflow:scroll; ">
                <input type="text" id="ticketSearch" name="" class="form-control form-control-lg"
                    placeholder="Search...">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mt-2" id="ticketList">


                </div>
            </div>
            {{-- Hidden trigger elements for JS event listeners (moved to dropdown) --}}
            <div style="display:none;">
                <div id="voucher-list"></div>
                <div id="sales-revenue"></div>
                <div id="cashier-closing"></div>
                <div id="ticket-sold-btn"></div>
            </div>

        </div>
        <div class="col-md-4">

            {{-- <div class="d-flex justify-content-between">
                <div>
                    <strong>Cashier:</strong> {{ auth()->user()->name }} <br>
                    <strong>Counter:</strong> {{ $shift->counter->name }}
                    <br><strong>Opened At:</strong> {{ \Carbon\Carbon::parse($shift->opened_at)->format('d M Y H:i') }}
                    <br><strong>Status:</strong> {{ ucfirst($shift->status) }}
                </div>
            </div> --}}

            <div style="height:50vh; overflow:scroll; " class=" shadow-sm border-top border-primary rounded-top border-5 ">

                <div class="cart-list mt-3 px-3" id="orderList"></div>

                <div class="mt-3 px-3" id="freeList"></div>

            </div>

            <div class="d-flex justify-content-between mt-3">
                <a href="javascript:;" id="deleteAll"></a>
                <a href="javascript:;" id="saveDraft"></a>

            </div>
            <div class="card mt-5 ">
                <div class="card-body shadow-sm">

                    <div class="d-flex justify-content-between mt-3">
                        <strong>TOTAL</strong>
                        <span class="fw-bold" id="orderTotal">Rp 0</span>
                    </div>

                    <button class="btn btn-primary btn-lg w-100 mt-3" id="pay">Pay Now</button>
                </div>


            </div>
        </div>
    </div>

    <div class="modal fade" id="ticketQtyModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Jumlah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="input-group">
                        <button class="btn btn-outline-secondary" type="button" id="decreaseQty">-</button>
                        <input type="number" id="ticketQtyInput" class="form-control text-center" value="1"
                            min="1" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="increaseQty">+</button>
                    </div>
                    <div class="mt-3 text-center">
                        <button id="submitQtyBtn" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="detailPaketModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailPackageLabel">Package Wahana</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body" id="detailPaketContent">
                    <!-- Diisi via JS -->
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modelListFreeVoucher" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailPackageLabel">Free Voucher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @foreach ($fg as $r)
                        <div class="kp-voucher-main" data-id="{{ $r['id'] }}">
                            <div class="kp-voucher-pattern"></div>
                            <div class="kp-voucher-content">
                                <div class="kp-voucher-title">FREE VOUCHER</div>
                                <div class="kp-voucher-description">{{ $r['name'] }}</div>
                                <small>{{ $r['description'] }}</small>

                                <div class="kp-voucher-list">
                                    <ul class="mb-0">
                                        @foreach ($r['wahanas'] as $w)
                                            <li>
                                                {{ $w['name'] }}
                                                @if (isset($w['price']))
                                                    - Rp {{ number_format($w['price'], 0, ',', '.') }}
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>


    <form action="{{ route('transaction.store') }}" method="POST" id="paymentForm">
        @csrf
        <div class="modal fade" id="paymentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pilih Metode Pembayaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <select name="payment_type" id="paymentType" class="form-select" required>
                                <option value="">Pilih</option>
                                <option value="cash">Cash</option>
                                <option value="noncash">Non-Cash</option>
                            </select>
                        </div>

                        <!-- CASH SECTION -->
                        <div id="cashSection" style="display: none;">
                            <label class="form-label">Jumlah Uang Diberikan</label>
                            {{-- <input type="text" name="amount_given" id="amountGiven" class="form-control "
                                 min="0" /> --}}

                            <div id="cashOptions" class="d-flex flex-wrap gap-2 mb-2 justify-content-beetween"></div>
                            <input type="hidden" name="amount_given" id="amountGiven" />

                            <div class="mt-2 text-muted">
                                <h2>Kembalian: <strong id="kembalianText">Rp 0</strong></h2>
                            </div>
                        </div>

                        <!-- NON CASH SECTION -->
                        <div id="nonCashSection" style="display: none;">
                            <label class="form-label">Metode Non-Cash</label>
                            <select name="noncash_method" id="noncashMethod" class="form-select mb-2">
                                <option value="">Pilih</option>
                                <option value="qris">QRIS</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="edc">EDC</option>
                                <option value="lainnya">Lainnya</option>
                            </select>

                            <label class="form-label">Non Cash Channel</label>
                            <select name="noncash_channel" id="noncashChannel" class="form-select">
                                <option value="">Pilih Channel</option>

                            </select>
                        </div>

                        <input type="hidden" name="total" id="paymentTotal" value="0" />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">Bayar Sekarang</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- ═══════════════════════════════════════════════════════════
         Transaction View Modal  (static — closes only via button)
         ═══════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="transactionViewModal" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-receipt me-2 text-primary"></i>Detail Transaksi
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                </div>
                <div class="modal-body" id="transactionViewBody">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2 text-muted">Memuat data transaksi...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script>
        document.documentElement.classList.add('layout-menu-collapsed');
        const orderList = [];


        const qtyInput = document.getElementById('ticketQtyInput');
        const btnPlus = document.getElementById("increaseQty");
        const btnMinus = document.getElementById("decreaseQty");


        const submitBtn = document.getElementById('submitQtyBtn');
        const ticketList = document.getElementById('ticketList');
        const orderListContainer = document.getElementById("orderList");
        const orderTotal = document.getElementById('orderTotal');
        const deleteAllLink = document.getElementById('deleteAll');
        const saveDraft = document.getElementById('saveDraft');
        const searchInput = document.getElementById('ticketSearch');
        let selectedTicket = null;
        let selectedDraftTicket = null;
        const ticket = @json($combined);

        const draftList = document.getElementById('draftList');
        let draft = @json($draft);

        const freeListContainer = document.getElementById('freeList');
        const freeList = [];
        const freeRule = [];
        const freeGiftRules = @json($fg);
        // console.log(freeGiftRules);

        let lastGiftCount = {}; // simpan jumlah gift terakhir per rule

        const checkFreeGift = () => {
            // 🔢 Hitung total harga orderList
            const totalHarga = orderList.reduce((sum, item) => {
                return sum + (item.price * item.qty);
            }, 0);


            fetch(`/transaction/check-free/${totalHarga}`)
                .then(response => {
                    if (!response.ok) throw new Error("Gagal fetch data");
                    return response.json();
                })
                .then(data => {
                    freeList.length = 0;
                    freeRule.length = 0;

                    data.wahana_free.forEach(item => {
                        freeList.push(item);
                    });

                    data.free_gifts.forEach(item => {
                        freeRule.push(item);
                    });


                    // console.log(freeList);


                    renderFreeRuleList();
                })
                .catch(err => {
                    console.error("Error:", err);
                });
        }



        const ticketComponent = (r) => {
            const detailEncoded = encodeURIComponent(JSON.stringify(r.detail ?? []));
            return ` <div class="col wahana-list ">
                 <div class="card wahana-card " data-id="${r.id}" data-price="${r.price}" data-type="${r.type}"  data-detail="${detailEncoded}">
                     ${r.type === 'package' ? '<div class="wahana-info"><i class="ti ti-info-circle"></i></div>' : ''}
                     <span class="wahana-number"></span>
                     <span class="wahana-name">${r.name}</span>

                 </div>
             </div>`;


        }

        const draftComponent = (r) => {


            return `<div class="draft-item p-3 border rounded shadow-sm small " data-id="${r.id}">
                <div class="fw-bold text-primary mb-2">Order : ${r.transaction_code}</div>
                <div><span class="text-muted">Total Item: </span>${r.total_item} </div>
                <div><span class="text-muted">Time:</span> ${r. created_at}</div>
            </div>`;
        }


        btnPlus.addEventListener("click", () => {
            qtyInput.value = parseInt(qtyInput.value) + 1;
        });

        btnMinus.addEventListener("click", () => {
            let current = parseInt(qtyInput.value);
            if (current > 1) {
                qtyInput.value = current - 1;
            }
        });
        const formatRupiah = (angka) => {
            return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        };

        const orderComponent = (r) => {
            return `
             <div class="cart-item d-flex justify-content-between align-items-center flex-wrap py-2 border-bottom order-item " data-id="${r.id}" data-type="${r.type}">
                <div class="flex-grow-1">
                    <div class="fw-bold">${r.name}</div>
                    <div class="text-muted small">x${r.qty}</div>
                </div>
                <div class="text-end">
                    <div class="fw-bold">${formatRupiah(r.qty * r.price)}</div>
                </div>
                <div>
                    <a href="javascript:;" class="ms-3 btn-remove-order">
                        <i class="ti ti-trash ti-sm"></i>
                    </a>
                </div>
            </div>`;
        };
        const freeComponent = (r) => {
            return `
             <div class="free-item d-flex justify-content-between align-items-center flex-wrap py-2 border-bottom order-item " data-id="${r.wahan_id}" >
                <div class="flex-grow-1">
                    <div class="fw-bold">${r.wahana}</div>
                    <div class="text-muted small">x${r.qty}</div>
                </div>
                <div class="text-end">
                    <div class="fw-bold">Free</div>
                </div>
            </div>`;
        };

        const freeRuleComponent = (r) => {


            //     const giftList = r.gifts && r.gifts.length > 0 ?
            //         `<ul class="kp-voucher-list">
        //       ${r.gifts.map(i => `<li>${i.wahana} @${i.qty}</li>`).join("")}
        //    </ul>` :
            //         ""; // kalau kosong, return string kosong


            const giftList = r.gifts && r.gifts.length > 0 ?
                `<div class="kp-voucher-list"> ${r.gifts.map(i => `${i.wahana} @${i.qty}`).join(" | ")} </div>` :
                `<div class="kp-voucher-list">-</div>`; // fallback kalau kosong


            return ` <div class="kp-voucher-main" data-id="${r.rule_id}">
                    <div class="kp-voucher-pattern"></div>
                    <div class="kp-voucher-content">
                        <div class="kp-voucher-title">FREE VOUCHER</div>
                        <div class="kp-voucher-discount">x${r.multiple}</div>
                        <div class="kp-voucher-description">${r.rule_name}</div>
                        ${giftList}
                    </div>

                </div>`;
        }


        const renderFreeList = () => {
            freeListContainer.innerHTML = '';
            freeList.forEach(item => {
                freeListContainer.insertAdjacentHTML('beforeend', freeComponent(item));
            })
        }
        const renderFreeRuleList = () => {
            console.log(freeRule);

            freeListContainer.innerHTML = '';
            freeRule.forEach(item => {
                freeListContainer.insertAdjacentHTML('beforeend', freeRuleComponent(item));
            })

        }
        const renderOrderList = () => {
            checkFreeGift();


            orderListContainer.innerHTML = '';

            let total = 0;
            let count = 0;

            // console.log(orderList);

            orderList.forEach(item => {
                orderListContainer.insertAdjacentHTML('beforeend', orderComponent(item));
                total += item.qty * item.price;
                count += 1;
            });

            // Tampilkan total
            orderTotal.textContent = formatRupiah(total);
            paymentTotal.value = total;

            // Update teks Delete All

            deleteAllLink.textContent = `Delete All (${count})`;
            saveDraft.textContent = 'Save Draft';
            deleteAllLink.style.display = count > 0 ? 'inline' : 'none';
            saveDraft.style.display = count > 0 ? 'inline' : 'none';
        };
        //  console.log(ticket);




        function renderTicketList(data) {
            ticketList.innerHTML = ''; // hapus semua dulu
            data.forEach(r => {
                const html = ticketComponent(r);
                ticketList.insertAdjacentHTML('beforeend', html);
            });
        }

        function renderDraftList(data) {


            draftList.innerHTML = ''; // hapus semua dulu
            data.forEach(r => {
                const html = draftComponent(r);
                draftList.insertAdjacentHTML('beforeend', html);
            });
        }
        draftList.addEventListener("click", function(e) {
            const card = e.target.closest('.draft-item');
            if (!card) return;

            // hilangkan active dari semua draft-item
            document.querySelectorAll(".draft-item").forEach(el => el.classList.remove("active"));

            // tambahkan active ke item yang diklik
            card.classList.add("active");

            const id = card.dataset.id;
            selectedDraftTicket = id;
            orderList.length = 0;
            fetch(`/transaction/get-detail/${id}`)
                .then(response => {
                    if (!response.ok) throw new Error("Gagal fetch data");
                    return response.json();
                })
                .then(data => {


                    data.forEach(item => {
                        orderList.push(item);
                    });
                    renderOrderList();


                })
                .catch(err => {
                    console.error("Error:", err);
                });

        });


        ticketList.addEventListener("click", function(e) {
            const infoEl = e.target.closest('.wahana-info');
            const cardEl = e.target.closest('.wahana-card');

            // Kalau klik di luar .wahana-card, abaikan
            if (!cardEl) return;

            if (infoEl && cardEl.contains(infoEl)) {
                const detailRaw = cardEl.dataset.detail;
                if (!detailRaw) return;

                let detail;
                try {
                    detail = JSON.parse(decodeURIComponent(detailRaw));
                } catch (err) {
                    return alert('Gagal membaca detail paket.');
                }

                if (!detail.length) {
                    document.getElementById('detailPaketContent').innerHTML =
                        '<em>Paket ini belum memiliki isi wahana.</em>';
                } else {
                    const list = detail.map(item => `
                        <li class="list-group-item d-flex justify-content-between">
                            <span>${item.name}</span>
                            <span class="badge bg-primary">x${item.qty}</span>
                        </li>
                    `).join('');

                    document.getElementById('detailPaketContent').innerHTML = `
                <ul class="list-group">${list}</ul>
            `;
                }

                // Tampilkan modal Bootstrap
                const modal = new bootstrap.Modal(document.getElementById('detailPaketModal'));
                modal.show();
                return;
            } else {
                const id = cardEl.dataset.id;
                const type = cardEl.dataset.type;
                const price = parseFloat(cardEl.dataset.price); // pastikan angka
                const name = cardEl.querySelector('.wahana-name')?.textContent.trim() ?? '';

                const existing = orderList.find(item => item.id === id && item.type === type);

                // Contoh: simpan ke selectedTicket
                selectedTicket = {
                    id: id,
                    name: name,
                    type: type,
                    price: price
                };
                qtyInput.value = existing ? existing.qty : 1;
                const modal = document.getElementById('ticketQtyModal');
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();

            }



        })


        // 2. Submit jumlah tiket
        submitBtn.addEventListener("click", function() {
            const qty = parseInt(qtyInput.value);
            if (qty < 1 || !selectedTicket) return alert("Jumlah tiket tidak valid.");

            // Cek apakah item dengan id & type sudah ada di orderList
            const existingIndex = orderList.findIndex(item =>
                item.id === selectedTicket.id && item.type === selectedTicket.type
            );

            if (existingIndex !== -1) {
                // Jika sudah ada, update qty
                orderList[existingIndex].qty = qty;
            } else {
                // Jika belum ada, tambahkan item baru
                orderList.push({
                    ...selectedTicket,
                    qty
                });
            }


            // console.log("Order List:", orderList);
            renderOrderList(); // 🔁 Render ulang setelah update
            const modal = document.getElementById('ticketQtyModal');
            const instance = bootstrap.Modal.getInstance(modal);
            if (instance) instance.hide();



            selectedTicket = null;
        });

        orderListContainer.addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove-order')) {
                const parent = e.target.closest('.order-item');
                const id = parent.dataset.id;
                const type = parent.dataset.type;

                // Hapus dari orderList
                const index = orderList.findIndex(item => item.id === id && item.type === type);
                if (index !== -1) {
                    orderList.splice(index, 1);
                    renderOrderList();
                }
            }
        });
        deleteAllLink.addEventListener('click', function(e) {
            e.preventDefault();

            if (confirm('Yakin ingin menghapus semua item?')) {
                orderList.length = 0; // kosongkan array

                if (selectedDraftTicket != null) {
                    fetch(`/transaction/delete-draft-transaction/${selectedDraftTicket}`)
                        .then(response => {
                            if (!response.ok) throw new Error("Gagal fetch data");
                            return response.json();
                        })
                        .then(res => {
                            if (res.status === "success") {
                                window.location.reload();
                            } else {
                                alert(res.message || "Gagal hapus draft");
                            }
                        })
                        .catch(err => {
                            console.error("Error:", err);
                        });
                }


                renderOrderList(); // render ulang




            }
        });

        searchInput.addEventListener('input', function() {
            const keyword = this.value.toLowerCase();

            const filtered = ticket.filter(item =>
                item.name.toLowerCase().includes(keyword)
            );

            renderTicketList(filtered);
        });
        renderTicketList(ticket);
        renderDraftList(draft);
    </script>


    <script>
        const payBtn = document.getElementById('pay');
        const modalEl = document.getElementById('paymentModal');
        const paymentType = document.getElementById('paymentType');
        const cashSection = document.getElementById('cashSection');
        const nonCashSection = document.getElementById('nonCashSection');
        const amountGiven = document.getElementById('amountGiven');
        const kembalianText = document.getElementById('kembalianText');
        const paymentTotal = document.getElementById('paymentTotal');
        const paymentForm = document.getElementById('paymentForm');

        const generateCashButtons = (totalBayar) => {
            const cashOptionsDiv = document.getElementById("cashOptions");
            cashOptionsDiv.innerHTML = '';

            const hasil = new Set();

            // Selalu mulai dari total bayar
            hasil.add(totalBayar);

            // Tambahkan pecahan-pecahan +5k, +10k, +20k, dst.
            const kelipatanTambahan = [5000, 10000, 20000, 30000, 50000];
            kelipatanTambahan.forEach(offset => {
                const calon = Math.ceil((totalBayar + offset) / 5000) * 5000;
                hasil.add(calon);
            });

            // Tambahkan pecahan standar jika lebih tinggi dari totalBayar
            const pecahanStandar = [50000, 100000, 200000];
            pecahanStandar.forEach(nominal => {
                if (nominal > totalBayar) hasil.add(nominal);
            });

            // Ubah ke array dan urutkan
            const jumlahFinal = Array.from(hasil).sort((a, b) => a - b);

            // Render tombol
            jumlahFinal.forEach(jml => {
                const btn = document.createElement("button");
                btn.type = "button";
                btn.className = "btn btn-outline-success";
                btn.textContent = formatRupiah(jml);
                btn.onclick = () => {
                    document.getElementById("amountGiven").value = jml;
                    const kembalian = jml - totalBayar;
                    document.getElementById("kembalianText").textContent = formatRupiah(kembalian);
                };
                cashOptionsDiv.appendChild(btn);
            });
        }





        payBtn.addEventListener('click', function() {
            if (!orderList.length) {
                alert("Silakan pilih tiket terlebih dahulu sebelum membayar.");
                return;
            }
            var type = paymentType.value;
            checkType(type);
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();


        });

        paymentType.addEventListener('change', function() {
            const type = this.value;
            checkType(type);

        });

        const noncashMethod = document.getElementById('noncashMethod');
        const noncashChannel = document.getElementById('noncashChannel');

        const channelOptions = {
            'qris': ['QRIS', 'GoPay', 'OVO', 'ShopeePay', 'Dana', 'LinkAja'],
            'transfer': ['BCA', 'Mandiri', 'BRI', 'BNI', 'BSI', 'CIMB Niaga'],
            'edc': ['BCA', 'Mandiri', 'BRI', 'BNI', 'CIMB Niaga'],
            'lainnya': ['Lainnya']
        };

        // Populate channel list on method change
        if (noncashMethod) {
            noncashMethod.addEventListener('change', function() {
                const method = this.value;
                // Clear existing options
                noncashChannel.innerHTML = '<option value="">Pilih Channel</option>';

                if (channelOptions[method]) {
                    channelOptions[method].forEach(opt => {
                        const option = document.createElement('option');
                        option.value = opt;
                        option.textContent = opt;
                        noncashChannel.appendChild(option);
                    });
                } else if (method === 'lainnya') {
                    const option = document.createElement('option');
                    option.value = 'Lainnya';
                    option.textContent = 'Lainnya';
                    noncashChannel.appendChild(option);
                }
            });
        }

        function checkType(type) {
            if (type === 'cash') {
                cashSection.style.display = 'block';
                nonCashSection.style.display = 'none';

                const totalBayar = parseFloat(paymentTotal.value);
                generateCashButtons(totalBayar);
            } else if (type === 'noncash') {
                cashSection.style.display = 'none';
                nonCashSection.style.display = 'block';
            } else {
                cashSection.style.display = 'none';
                nonCashSection.style.display = 'none';
            }
        }


        function createElementSubmit() {
            const existing = document.querySelector('input[name="order_list"]');
            if (existing) existing.remove(); // hapus duplikat jika ada

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order_list';
            input.value = JSON.stringify(orderList);
            paymentForm.appendChild(input);


            const existing2 = document.querySelector('input[name="free_rule"]');
            if (existing2) existing2.remove(); // hapus duplikat jika ada

            const input2 = document.createElement('input');
            input2.type = 'hidden';
            input2.name = 'free_rule';
            input2.value = JSON.stringify(freeRule);
            paymentForm.appendChild(input2);

        }
        saveDraft.addEventListener("click", function() {
            if (confirm('Yakin ingin menyimpan data ini')) {
                // hapus input draft lama biar gak duplikat
                const existingDraft = paymentForm.querySelector('input[name="draft"]');
                if (existingDraft) existingDraft.remove();

                const draftInput = document.createElement('input');
                draftInput.type = 'hidden';
                draftInput.name = 'draft';
                draftInput.value = 'true';
                paymentForm.appendChild(draftInput);


                if (selectedDraftTicket != null) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'transaction_id';
                    input.value = selectedDraftTicket
                    paymentForm.appendChild(input);
                }
                createElementSubmit();

                // submit form
                paymentForm.submit();
            }
        });



        const voucherList = document.getElementById('voucher-list');
        voucherList.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('modelListFreeVoucher'));
            modal.show();
            return;
        });

        document.getElementById('cashier-closing').addEventListener("click", function() {
            window.location.href = "{{ route('transaction.close-shift') }}";
        });
        document.getElementById('sales-revenue').addEventListener("click", function() {
            window.open("{{ route('transaction.sales-revenue') }}", '_blank');
        });
    </script>


    <script src="{{ asset('js/bluetooth-printer.js') }}"></script>
    <script>
        // ─────────────────────────────────────────────────────────────
        // AJAX Payment → load view-partial into static modal
        // ─────────────────────────────────────────────────────────────
        (function() {
            const form = document.getElementById('paymentForm');
            if (!form) return;

            form.addEventListener('submit', async function(e) {
                // Draft submit: let it go through as normal form submit
                if (form.querySelector('input[name="draft"]')) return;

                e.preventDefault();

                // 🔒 Client-side validation (same rules as before)
                const type = paymentType.value;

                // Prep hidden inputs
                const existingDraft = form.querySelector('input[name="draft"]');
                if (existingDraft) existingDraft.remove();

                if (selectedDraftTicket != null) {
                    const existingTxnId = form.querySelector('input[name="transaction_id"]');
                    if (existingTxnId) existingTxnId.remove();
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'transaction_id';
                    inp.value = selectedDraftTicket;
                    form.appendChild(inp);
                }

                createElementSubmit();

                if (type === 'cash') {
                    const given = parseFloat(amountGiven.value);
                    const total = parseFloat(paymentTotal.value);
                    if (given < total) {
                        alert('Uang yang diberikan kurang dari total yang harus dibayar.');
                        return;
                    }
                }

                if (type === 'noncash') {
                    const method = document.getElementById('noncashMethod').value;
                    const channel = document.getElementById('noncashChannel').value;
                    amountGiven.value = paymentTotal.value;
                    if (!method) {
                        alert('Silakan pilih metode pembayaran non-cash.');
                        return;
                    }
                    if (!channel) {
                        alert('Silakan pilih channel pembayaran.');
                        return;
                    }
                }

                const btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                }

                try {
                    const fd = new FormData(form);
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: fd
                    });

                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Gagal menyimpan transaksi.');
                    }

                    const data = await res.json();
                    if (data.status !== 'ok') throw new Error(data.message || 'Terjadi kesalahan.');

                    // Tutup payment modal
                    const payModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                    if (payModal) payModal.hide();

                    // Reset semua state ke kondisi awal
                    orderList.length = 0;
                    selectedDraftTicket = null;
                    freeRule.length = 0;
                    renderOrderList();
                    resetPaymentForm();

                    // Load view-partial ke modal
                    await openTransactionViewModal(data.id);

                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: err.message
                    });
                } finally {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = 'Bayar Sekarang';
                    }
                }
            });
        })();

        function resetPaymentForm() {
            // Metode pembayaran → pilih (blank)
            const payType = document.getElementById('paymentType');
            if (payType) payType.value = '';

            // Sembunyikan cash & noncash section
            const cashSection = document.getElementById('cashSection');
            const nonCashSection = document.getElementById('nonCashSection');
            if (cashSection) cashSection.style.display = 'none';
            if (nonCashSection) nonCashSection.style.display = 'none';

            // Reset jumlah uang & kembalian
            const amountGivenEl = document.getElementById('amountGiven');
            if (amountGivenEl) amountGivenEl.value = '';
            const kembalianText = document.getElementById('kembalianText');
            if (kembalianText) kembalianText.textContent = 'Rp 0';

            // Hapus tombol cash options yang di-generate dinamis
            const cashOptions = document.getElementById('cashOptions');
            if (cashOptions) cashOptions.innerHTML = '';

            // Reset Non-Cash dropdowns
            const noncashMethod = document.getElementById('noncashMethod');
            const noncashChannel = document.getElementById('noncashChannel');
            if (noncashMethod) noncashMethod.value = '';
            if (noncashChannel) {
                noncashChannel.innerHTML = '<option value="">Pilih Channel</option>';
            }

            // Hapus hidden input dinamis (draft, transaction_id, order_list, free_rule)
            ['draft', 'transaction_id', 'order_list', 'free_rule'].forEach(name => {
                const el = document.querySelector(`input[name="${name}"]`);
                if (el) el.remove();
            });

            // Reset total
            const payTotal = document.getElementById('paymentTotal');
            if (payTotal) payTotal.value = '0';
        }

        async function openTransactionViewModal(id) {
            const body = document.getElementById('transactionViewBody');
            const modal = new bootstrap.Modal(document.getElementById('transactionViewModal'));

            // Show loading state
            body.innerHTML =
                '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted">Memuat data transaksi...</p></div>';
            modal.show();

            try {
                const res = await fetch(`{{ url('/transaction/view-partial') }}/${id}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) throw new Error('Gagal memuat detail transaksi.');
                body.innerHTML = await res.text();
                // Re-init connect button state
                if (typeof updateConnectBtn === 'function') updateConnectBtn();
            } catch (err) {
                body.innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
            }
        }

        // ─────────────────────────────────────────────────────────────
        // Connect Printer helpers (shared: used by partial too)
        // ─────────────────────────────────────────────────────────────
        function updateConnectBtn() {
            const btns = document.querySelectorAll('#btnConnectBT, .btnConnectBT');
            btns.forEach(btn => {
                const label = btn.querySelector('#btnConnectBTLabel, .btnConnectBTLabel') || btn.nextElementSibling;
                const dot = btn.querySelector('#printerDot, .printerDot');

                if (window.BTPrinter?.isConnected) {
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-outline-success', 'connected');
                    if (dot) dot.classList.add('connected');
                    if (label) label.textContent = 'Printer Connected ✓';
                } else {
                    btn.classList.remove('btn-outline-success', 'connected');
                    btn.classList.add('btn-outline-secondary');
                    if (dot) dot.classList.remove('connected');
                    if (label) label.textContent = 'Connect Printer';
                }
            });
        }

        async function connectBTPrinter() {
            const btn = document.getElementById('btnConnectBT');
            const label = document.getElementById('btnConnectBTLabel');
            if (!btn) return;
            btn.disabled = true;
            label.textContent = 'Connecting...';

            try {
                await window.BTPrinter.connectOrReconnect();
                btn.disabled = false;
                updateConnectBtn();
                Swal.fire({
                    icon: 'success',
                    title: 'Printer terhubung!',
                    timer: 1200,
                    showConfirmButton: false
                });
            } catch (e) {
                btn.disabled = false;
                label.textContent = 'Connect Printer';
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Gagal reconnect',
                    text: 'Printer tidak merespons. Buka scan Bluetooth untuk pair ulang?',
                    showCancelButton: true,
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                    buttonsStyling: false,
                    confirmButtonText: 'Buka Scan',
                    cancelButtonText: 'Batal'
                });
                if (result.isConfirmed) {
                    const ok = await window.BTPrinter.connect();
                    updateConnectBtn();
                    if (ok) Swal.fire({
                        icon: 'success',
                        title: 'Printer terhubung!',
                        timer: 1200,
                        showConfirmButton: false,
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                        buttonsStyling: false
                    });
                }
            }
        }

        document.addEventListener('btPrinterConnected', updateConnectBtn);
        document.addEventListener('btPrinterDisconnected', updateConnectBtn);

        // ─────────────────────────────────────────────────────────────
        // Print Bill (Receipt)
        // ─────────────────────────────────────────────────────────────
        async function printBill(id) {
            const btn = document.getElementById('btnPrintBill');
            let billPrinted = false;
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mencetak...';
            }
            try {
                const connected = await window.BTPrinter.connectOrReconnect();
                if (!connected) throw new Error('Printer tidak terkoneksi.');

                const res = await fetch(`{{ url('/printer/transaction-data') }}/${id}`);
                if (!res.ok) throw new Error('Gagal mengambil data struk.');
                const data = await res.json();

                const bytes = window.BTPrinter.buildReceipt(data);
                const ok = await window.BTPrinter.sendData(bytes);
                if (ok) {
                    await fetch('{{ route('transaction.print-bill') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            transaction_id: id,
                            _bt: 1
                        })
                    });
                    billPrinted = true;
                    Swal.fire({
                        icon: 'success',
                        title: 'Struk dicetak!',
                        timer: 1500,
                        showConfirmButton: false,
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                        buttonsStyling: false
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: e.message,
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                    buttonsStyling: false
                });
            } finally {
                if (btn) {
                    if (billPrinted) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-check me-1"></i> Sudah Dicetak';
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-print me-1"></i> Print Bill';
                    }
                }
            }
        }

        // ─────────────────────────────────────────────────────────────
        // Print Single Ticket
        // ─────────────────────────────────────────────────────────────
        async function printTicket(id, el) {
            const icon = el?.querySelector('i');
            const origClass = icon?.className;
            if (icon) icon.className = 'fas fa-spinner fa-spin ti-sm';
            try {
                const connected = await window.BTPrinter.connectOrReconnect();
                if (!connected) throw new Error('Printer tidak terkoneksi.');

                const res = await fetch(`{{ url('/printer/ticket-data') }}/${id}`);
                if (!res.ok) throw new Error('Gagal mengambil data tiket.');
                const data = await res.json();

                const bytes = window.BTPrinter.buildTicket(data);
                const ok = await window.BTPrinter.sendData(bytes);
                if (ok) {
                    const flagRes = await fetch('{{ route('transaction.print-ticket') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id,
                            _bt: 1
                        })
                    });
                    const flagData = await flagRes.json();
                    if (flagData.status === 'success') {
                        const row = el?.closest('.order-item');
                        if (row) {
                            row.querySelectorAll('.fw-bold, .text-muted').forEach(e => e.classList.add('ticket-used'));
                            el.classList.add('ticket-used');
                        }

                        // Disable Print All Ticket btn if no more active tickets
                        const activeTickets = document.querySelectorAll('.btn-remove-order:not(.ticket-used)');
                        if (activeTickets.length === 0) {
                            const btnAll = document.getElementById('btnPrintTicketAll');
                            if (btnAll) btnAll.disabled = true;
                        }
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Tiket dicetak!',
                        timer: 1000,
                        showConfirmButton: false,
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                        buttonsStyling: false
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: e.message,
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                    buttonsStyling: false
                });
            } finally {
                if (icon) icon.className = origClass;
            }
        }

        // ─────────────────────────────────────────────────────────────
        // Print All Tickets
        // ─────────────────────────────────────────────────────────────
        async function printTicketAll(transactionId) {
            const btn = document.getElementById('btnPrintTicketAll');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mencetak...';
            let permanentlyDisabled = false;
            try {
                const connected = await window.BTPrinter.connectOrReconnect();
                if (!connected) throw new Error('Printer tidak terkoneksi.');

                const res = await fetch(`{{ url('/printer/tickets-data') }}/${transactionId}`);
                if (!res.ok) throw new Error('Gagal mengambil data tiket.');
                const {
                    tickets
                } = await res.json();

                let printed = 0;
                for (const t of tickets) {
                    const bytes = window.BTPrinter.buildTicket(t);
                    const ok = await window.BTPrinter.sendData(bytes);
                    if (ok) {
                        printed++;
                        await new Promise(r => setTimeout(r, 400));
                    }
                }

                if (printed > 0) {
                    const flagRes = await fetch('{{ route('transaction.print-ticket-all') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            transaction_id: transactionId,
                            _bt: 1
                        })
                    });
                    const flagData = await flagRes.json();
                    if (flagData.status === 'success') {
                        document.querySelectorAll('.order-item').forEach(row => {
                            row.querySelectorAll('.fw-bold, .text-muted').forEach(e => e.classList.add(
                                'ticket-used'));
                            const link = row.querySelector('a');
                            if (link) link.classList.add('ticket-used');
                        });
                        permanentlyDisabled = true;
                    }
                    Swal.fire({
                        icon: 'success',
                        title: `${printed} tiket dicetak!`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: e.message,
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                    buttonsStyling: false,
                });
            } finally {
                if (permanentlyDisabled) {
                    btn.innerHTML = '<i class="fas fa-check me-1"></i> Sudah Dicetak';
                    btn.disabled = true;
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-print me-1"></i> Print Ticket';
                }
            }
        }

        // ─────────────────────────────────────────────────────────────
        // Re-print (supervisor approval) — used from view-partial
        // ─────────────────────────────────────────────────────────────
        function confirmRePrint(id) {
            Swal.fire({
                title: 'Supervisor Approval',
                html: '<p class="mb-0">Masukkan PIN Supervisor untuk mencetak kembali tiket ini.</p>',
                target: document.getElementById('transactionViewModal') || document.body,
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off',
                    placeholder: 'PIN Password (4–6 digit)',
                    minlength: '4',
                    maxlength: '6'
                },
                customClass: {
                    confirmButton: 'btn btn-primary waves-effect waves-light me-2',
                    cancelButton: 'btn btn-outline-secondary'
                },
                buttonsStyling: false,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-print me-1"></i> Reprint',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: async (password) => {
                    if (!password) {
                        Swal.showValidationMessage('PIN wajib diisi');
                        return false;
                    }
                    if (password.length < 4 || password.length > 6) {
                        Swal.showValidationMessage('PIN harus 4–6 karakter');
                        return false;
                    }

                    try {
                        const res = await fetch('{{ route('transaction.reprint') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id: id,
                                spv_code: password
                            })
                        });
                        const data = await res.json();

                        if (res.ok && data.status === 'success') {
                            return data;
                        } else {
                            Swal.showValidationMessage(data.message || 'Gagal mereset tiket');
                            return false;
                        }
                    } catch (e) {
                        Swal.showValidationMessage('Terjadi kesalahan jaringan');
                        return false;
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Unflag everything in UI
                    document.querySelectorAll('.order-item').forEach(row => {
                        row.querySelectorAll('.ticket-used').forEach(e => e.classList.remove(
                            'ticket-used'));
                    });

                    // Re-enable Print All Ticket button
                    const btnAll = document.getElementById('btnPrintTicketAll');
                    if (btnAll) btnAll.disabled = false;

                    // Re-enable Print Bill button
                    const btnBill = document.getElementById('btnPrintBill');
                    if (btnBill) {
                        btnBill.disabled = false;
                        btnBill.className = 'btn btn-primary w-100 mt-3';
                        btnBill.innerHTML = '<i class="fas fa-print me-1"></i> Print Bill';
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Siap Cetak Ulang',
                        text: 'Silakan cetak kembali tiket atau struk.',
                        timer: 1500,
                        showConfirmButton: false,
                        target: document.getElementById('transactionViewModal') || document.body,
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                }
            });
        }
    </script>

@endsection
