<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Wahana</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex align-items-center gap-2">
            <!-- Filter Mode -->
            <select id="timeFilter" class="form-select w-auto">
                <option value="day">Hari ini (Jam)</option>
                <option value="week">Minggu ini (Hari)</option>
                <option value="month">Bulan ini (Tanggal)</option>
                <option value="lastmonth">Bulan lalu</option>
                <option value="year">Tahun ini</option>
                <option value="custom">Custom Range</option>
            </select>

            <!-- Date Range Picker (Hidden by default) -->
            <div id="customRange" class="d-flex align-items-center gap-2 d-none">
                <input type="date" id="startDate" class="form-control ">
                <span class="fw-bold">s/d</span>
                <input type="date" id="endDate" class="form-control ">
                <button class="btn btn-primary " id="applyRange">Terapkan</button>
            </div>
        </div>
    </div>

    <!-- Ringkasan -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Total Transaksi</h6>
                    <h4 class="fw-bold" id="totalTransaksi">1,234</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Total Tiket Terjual</h6>
                    <h4 class="fw-bold" id="totalTiket">3,450</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Total Tiket Gratis</h6>
                    <h4 class="fw-bold" id="tiketGratis">245</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Pendapatan Hari Ini</h6>
                    <h4 class="fw-bold text-success" id="pendapatan">Rp 5.200.000</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="row g-4">
        <!-- Penjualan Harian -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Penjualan</h6>
                </div>
                <div class="card-body">
                    <div id="chartPenjualan"></div>
                </div>
            </div>
        </div>
        <!-- Metode Pembayaran -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Distribusi Metode Pembayaran</h6>
                </div>
                <div class="card-body">
                    <div id="chartPayment"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <!-- Wahana Terlaris -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Top 5 Wahana Terlaris</h6>
                </div>
                <div class="card-body">
                    <div id="chartWahana"></div>
                </div>
            </div>
        </div>
        <!-- Highlight -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Highlight</h6>
                </div>
                <div class="card-body">
                    <p><strong>Pengunjung Terbanyak:</strong> Sabtu, Jam 15:00</p>
                    <p><strong>Promo Terpakai:</strong> 120 Tiket Gratis</p>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Script Chart -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const timeFilter = document.getElementById("timeFilter");
            const customRange = document.getElementById("customRange");

            timeFilter.addEventListener("change", function() {
                if (this.value === "custom") {
                    customRange.classList.remove("d-none");
                } else {
                    customRange.classList.add("d-none");
                }
            });

            document.getElementById("applyRange").addEventListener("click", function() {
                const start = document.getElementById("startDate").value;
                const end = document.getElementById("endDate").value;
                if (start && end) {
                    console.log("Filter Custom Range:", start, "s/d", end);
                    // TODO: Call API / update chart sesuai tanggal
                } else {
                    alert("Silakan pilih tanggal mulai dan tanggal akhir");
                }
            });
        });
        // Chart Penjualan
        let optionsPenjualan = {
            chart: {
                type: 'line',
                height: 300
            },
            series: [{
                name: 'Tiket Terjual',
                data: [30, 50, 40, 70, 90, 60, 120]
            }],
            xaxis: {
                categories: ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00']
            }
        };
        let chartPenjualan = new ApexCharts(document.querySelector("#chartPenjualan"), optionsPenjualan);
        chartPenjualan.render();

        // Chart Payment
        let optionsPayment = {
            chart: {
                type: 'pie',
                height: 300
            },
            labels: ['Cash', 'QRIS', 'Debit', 'E-Wallet'],
            series: [44, 33, 15, 8]
        };
        let chartPayment = new ApexCharts(document.querySelector("#chartPayment"), optionsPayment);
        chartPayment.render();

        // Chart Wahana
        let optionsWahana = {
            chart: {
                type: 'bar',
                height: 300
            },
            series: [{
                name: 'Tiket',
                data: [320, 250, 200, 180, 160]
            }],
            xaxis: {
                categories: ['Bom bom car', 'VR', 'Memanah', 'Kereta Dino', 'Flying Fox']
            }
        };
        let chartWahana = new ApexCharts(document.querySelector("#chartWahana"), optionsWahana);
        chartWahana.render();

        // Filter logika
        document.getElementById("timeFilter").addEventListener("change", function() {
            let value = this.value;
            if (value === "day") {
                chartPenjualan.updateOptions({
                    xaxis: {
                        categories: ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00']
                    }
                });
            } else if (value === "week") {
                chartPenjualan.updateOptions({
                    xaxis: {
                        categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']
                    }
                });
            } else if (value === "month") {
                chartPenjualan.updateOptions({
                    xaxis: {
                        categories: ['1', '5', '10', '15', '20', '25', '30']
                    }
                });
            } else if (value === "lastmonth") {
                chartPenjualan.updateOptions({
                    xaxis: {
                        categories: ['1', '5', '10', '15', '20', '25']
                    }
                });
            } else if (value === "year") {
                chartPenjualan.updateOptions({
                    xaxis: {
                        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt',
                            'Nov', 'Des'
                        ]
                    }
                });
            }
        });
    </script>
</body>

</html>
