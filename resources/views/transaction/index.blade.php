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
    </style>

@endsection
@section('content')

    <div class="row">
        <div class="col-md-7">
            <input type="text" id="ticketSearch" name="" class="form-control form-control-lg" placeholder="Search...">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mt-2" id="ticketList"></div>

        </div>
        <div class="col-md-5">
            <div class="title-order ">Order</div>
            <div style="height:55vh; overflow:scroll; " class="shadow-sm border-top border-primary rounded-top border-5 ">

                <div class="cart-list mt-3 px-3" id="orderList"></div>
            </div>
            <div class="text-center mt-3">
                <a href="javascript:;" id="deleteAll"></a>
            </div>
            <div class="card mt-5 ">
                <div class="card-body shadow-sm">
                    <div class="d-flex justify-content-between">
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
                                <small>Kembalian: <strong id="kembalianText">Rp 0</strong></small>
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
        const searchInput = document.getElementById('ticketSearch');
        let selectedTicket = null;
        const ticket = @json($combined);

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

        const renderOrderList = () => {

            orderListContainer.innerHTML = '';

            let total = 0;
            let count = 0;

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
            deleteAllLink.style.display = count > 0 ? 'inline' : 'none';
        };
        //  console.log(ticket);




        function renderTicketList(data) {
            ticketList.innerHTML = ''; // hapus semua dulu
            data.forEach(r => {
                const html = ticketComponent(r);
                ticketList.insertAdjacentHTML('beforeend', html);
            });
        }
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
            //  console.log("Order List:", orderList);
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
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();


        });

        paymentType.addEventListener('change', function() {
            const type = this.value;
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
        });



        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const type = paymentType.value;

            // 🔁 Inject orderList ke hidden input
            const existing = document.querySelector('input[name="order_list"]');
            if (existing) existing.remove(); // hapus duplikat jika ada

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order_list';
            input.value = JSON.stringify(orderList);
            this.appendChild(input);

            // 🔒 Validasi cash
            if (type === 'cash') {
                const given = anAmount.getNumber();
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

                if (!method || !bank) {
                    e.preventDefault();
                    alert('Silakan pilih metode dan bank untuk pembayaran non-cash.');
                    return;
                }
            }

            // Untuk noncash bisa tambahkan validasi lain di sini kalau perlu
        });
    </script>

@endsection
