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
    </style>

@endsection
@section('content')
    <div class="d-flex flex-wrap gap-3 mb-4" id="draftList"></div>

    <div class="row">
        <div class="col-md-8">
            <div style="height:55vh; overflow:scroll; ">
                <input type="text" id="ticketSearch" name="" class="form-control form-control-lg"
                    placeholder="Search...">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mt-2" id="ticketList">


                </div>
            </div>
            <hr>
            <div class="row mt-4 text-center menu">
                <div class="col-6 col-md-3 mb-3" id="voucher-list">
                    <button class="btn btn-outline-primary w-100 py-4 d-flex flex-column align-items-center">
                        <i class="fas fa-ticket-alt fa-2x mb-2"></i>
                        <span>Voucher List</span>
                    </button>
                </div>

                <div class="col-6 col-md-3 mb-3" id="sales-revenue">
                    <button class="btn btn-outline-success w-100 py-4 d-flex flex-column align-items-center">
                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                        <span>Sales Revenue</span>
                    </button>
                </div>

                <div class="col-6 col-md-3 mb-3" id="cashier-closing">
                    <button class="btn btn-outline-danger w-100 py-4 d-flex flex-column align-items-center">
                        <i class="fas fa-cash-register fa-2x mb-2"></i>
                        <span>Cashier Closing</span>
                    </button>
                </div>

            </div>

        </div>
        <div class="col-md-4">

            <div class="d-flex justify-content-between">
                <div>
                    <strong>Cashier:</strong> {{ auth()->user()->name }} <br>
                    <strong>Counter:</strong> {{ $shift->counter->name }}
                </div>
                <div class="text-end">
                    <strong>Opened At:</strong> {{ \Carbon\Carbon::parse($shift->opened_at)->format('d M Y H:i') }} <br>
                    <strong>Status:</strong> {{ ucfirst($shift->status) }}
                </div>
            </div>

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
                                <option value="debit">Kartu Debit</option>
                                <option value="lainnya">Lainnya</option>
                            </select>

                            <label class="form-label">Pilih Bank</label>
                            <select name="bank" id="bankList" class="form-select">
                                <option value="">Pilih Bank</option>
                                <option value="bca">BCA</option>
                                <option value="bni">BNI</option>
                                <option value="bri">BRI</option>
                                <option value="mandiri">Mandiri</option>
                                <option value="cimb">CIMB</option>
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

        paymentForm.addEventListener('submit', function(e) {

            const type = paymentType.value;


            const existingDraft = paymentForm.querySelector('input[name="draft"]');
            if (existingDraft) existingDraft.remove(); // hapus duplikat jika ada

            if (selectedDraftTicket != null) {

                const existingSelectedDraft = paymentForm.querySelector('input[name="transaction_id"]');
                if (existingSelectedDraft) existingSelectedDraft.remove(); // hapus duplikat jika ada

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'transaction_id';
                input.value = selectedDraftTicket
                paymentForm.appendChild(input);
            }

            createElementSubmit();


            // 🔒 Validasi cash
            if (type === 'cash') {
                const given = amountGiven.value;
                const total = parseFloat(paymentTotal.value);


                if (given < total) {
                    e.preventDefault();
                    alert('Uang yang diberikan kurang dari total yang harus dibayar.');
                    return;
                }


            }

            // 🔒 Validasi non-cash
            if (type === 'noncash') {
                const method = document.getElementById('noncashMethod').value;
                const bank = document.getElementById('bankList').value;
                amountGiven.value = paymentTotal.value;

                if (!method) {
                    e.preventDefault();
                    alert('Silakan pilih metode pembayaran non-cash.');
                    return;
                }

                // Kalau bukan QRIS, wajib pilih bank
                if (method !== 'qris' && !bank) {
                    e.preventDefault();
                    alert('Silakan pilih bank untuk metode pembayaran ini.');
                    return;
                }
            }
            // Untuk noncash bisa tambahkan validasi lain di sini kalau perlu
        });

        const voucherList = document.getElementById('voucher-list');
        voucherList.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('modelListFreeVoucher'));
            modal.show();
            return;
        });

        document.getElementById('cashier-closing').addEventListener("click", function() {
            window.location.href = "{{ route('transaction.close-shift') }}";
        })
        document.getElementById('sales-revenue').addEventListener("click", function() {
            window.location.href = "{{ route('transaction.sales-revenue') }}";
        })
    </script>

@endsection
