 @extends('layouts.main-layout.app')
 @section('title', 'Ticket')
 @section('content')
     <div class="d-flex align-items-center gap-2">
         <!-- Filter Mode -->
         <select id="timeFilter" class="form-select w-auto">
             <option value="day">Today (Hour)</option>
             <option value="week">This Week (Day)</option>
             <option value="lastweek">Last Week (Day)</option>
             <option value="month">This Month (Date)</option>
             <option value="lastmonth">Last Month (Date)</option>
             <option value="year">This Year (Month)</option>
             <option value="custom">Custom Range (Date)</option>
         </select>

         <!-- Date Range Picker (hidden by default) -->
         <div id="customRange" class="d-flex align-items-center gap-2 d-none">
             <input type="date" id="startDate" class="form-control">
             <span class="fw-bold">to</span>
             <input type="date" id="endDate" class="form-control">
             <button class="btn btn-primary" id="applyRange">
                 <i class="ti ti-search"></i>
             </button>
         </div>
     </div>


     <!-- KPI Cards -->
     <div class="row g-3 mb-4 mt-3">
         <div class="col-md-3">
             <div class="card shadow-sm border-0">
                 <div class="card-body">
                     <h6>Total Transactions</h6>
                     <h4 class="fw-bold" id="totalTransactions">0</h4>
                 </div>
             </div>
         </div>
         <div class="col-md-3">
             <div class="card shadow-sm border-0">
                 <div class="card-body">
                     <h6>Tickets Sold</h6>
                     <h4 class="fw-bold" id="ticketsSold">0</h4>
                 </div>
             </div>
         </div>
         <div class="col-md-3">
             <div class="card shadow-sm border-0">
                 <div class="card-body">
                     <h6>Free Tickets</h6>
                     <h4 class="fw-bold" id="ticketsFree">0</h4>
                 </div>
             </div>
         </div>
         <div class="col-md-3">
             <div class="card shadow-sm border-0">
                 <div class="card-body">
                     <h6>Revenue</h6>
                     <h4 class="fw-bold text-success" id="revenue">0</h4>
                 </div>
             </div>
         </div>
     </div>

     <!-- Chart Section -->
     <div class="row g-4">
         <!-- Penjualan Harian -->
         <div class="col-lg-12">
             <div class="card shadow-sm border-0">
                 <div class="card-header bg-white">
                     <h6 class="mb-0">Revenue</h6>
                 </div>
                 <div class="card-body">
                     <div id="chartPenjualan"></div>
                 </div>
             </div>
         </div>
     </div>
     <div class="row  mt-3 g-4">
         <!-- Metode Pembayaran -->
         <div class="col-lg-6">
             <div class="card shadow-sm border-0">
                 <div class="card-header bg-white">
                     <h6 class="mb-0">Ticket Sold</h6>
                 </div>
                 <div class="card-body">
                     <div id="chartTicket"></div>
                 </div>
             </div>
         </div>
         <div class="col-lg-6">
             <div class="card shadow-sm border-0">
                 <div class="card-header bg-white">
                     <h6 class="mb-0">Ticket Free</h6>
                 </div>
                 <div class="card-body">
                     <div id="chartTicketFree"></div>
                 </div>
             </div>
         </div>
     </div>
 @endsection
 @section('script')
     <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
     <script>
         const totalTransaction = document.getElementById('totalTransactions');
         const revenue = document.getElementById('revenue');
         const ticketsSold = document.getElementById('ticketsSold');
         const ticketsFree = document.getElementById('ticketsFree');
         let chartPenjualan; // simpan di global
         let chartTransaction; // simpan di global
         let chartTicket; // simpan di global
         let chartTicketFree; // simpan di global


         function fetchDashboard(params) {
             let url = "/dashboard/stat?" + new URLSearchParams(params);

             fetch(url)
                 .then(res => {
                     if (!res.ok) throw new Error("HTTP status " + res.status);
                     return res.json();
                 })
                 .then(data => {
                     console.log("Statistik:", data);
                     console.log(data.totalTransaction);

                     totalTransaction.textContent = data.totalTransaction ?? 0;
                     revenue.textContent = data.totalOmset ?? 'Rp. 0';
                     ticketsSold.textContent = data.totalTicketSold ?? 0;
                     ticketsFree.textContent = data.totalTicketFree ?? 0;


                     // Gabungan revenue & transaction
                     let options = {
                         chart: {
                             type: 'line',
                             height: 300
                         },
                         series: [{
                                 name: 'Omset',
                                 data: data.penjualan.data
                             },
                             {
                                 name: 'Transaction',
                                 data: data.transaction.data
                             }
                         ],
                         xaxis: {
                             categories: data.penjualan.categories
                         },
                         stroke: {
                             curve: 'smooth'
                         },
                         dataLabels: {
                             enabled: true
                         },
                         yaxis: [{
                                 title: {
                                     text: "Omset (Rp)"
                                 },
                                 labels: {
                                     formatter: function(val) {
                                         return "Rp " + val.toLocaleString("id-ID");
                                     }
                                 }
                             },
                             {
                                 opposite: true,
                                 title: {
                                     text: "Transaction"
                                 }
                             }
                         ],
                         tooltip: {
                             y: {
                                 formatter: function(val, {
                                     seriesIndex
                                 }) {
                                     if (seriesIndex === 0) {
                                         return "Rp " + val.toLocaleString("id-ID");
                                     }
                                     return val;
                                 }
                             }
                         }
                     };

                     if (!chartPenjualan) {
                         chartPenjualan = new ApexCharts(document.querySelector("#chartPenjualan"), options);
                         chartPenjualan.render();
                     } else {
                         chartPenjualan.updateOptions(options);
                     }


                     let optionsTicket = {
                         chart: {
                             type: 'bar',
                             height: 300
                         },
                         series: [{
                             name: 'Tiket',
                             data: data.ticket.data
                         }],
                         xaxis: {
                             categories: data.ticket.categories
                         }
                     };

                     if (!chartTicket) {
                         chartTicket = new ApexCharts(document.querySelector("#chartTicket"), optionsTicket);
                         chartTicket.render();
                     } else {
                         chartTicket.updateOptions(optionsTicket);
                     }



                     let optionsTicketFree = {
                         chart: {
                             type: 'bar',
                             height: 300
                         },
                         series: [{
                             name: 'Tiket',
                             data: data.ticket_free.data
                         }],
                         xaxis: {
                             categories: data.ticket_free.categories
                         }
                     };

                     if (!chartTicketFree) {
                         chartTicketFree = new ApexCharts(document.querySelector("#chartTicketFree"), optionsTicketFree);
                         chartTicketFree.render();
                     } else {
                         chartTicketFree.updateOptions(optionsTicketFree);
                     }

                 })
                 .catch(err => console.error("Fetch error:", err));
         }

         document.addEventListener("DOMContentLoaded", function() {


             const timeFilter = document.getElementById("timeFilter");
             const customRange = document.getElementById("customRange");
             const applyRange = document.getElementById("applyRange");

             if (!timeFilter || !customRange || !applyRange) return;

             timeFilter.addEventListener("change", function() {
                 if (this.value === "custom") {
                     customRange.classList.remove("d-none");
                 } else {
                     customRange.classList.add("d-none");
                     fetchDashboard({
                         filter: this.value
                     });
                 }
             });

             applyRange.addEventListener("click", function() {
                 const start = document.getElementById("startDate")?.value;
                 const end = document.getElementById("endDate")?.value;

                 if (!start || !end) {
                     alert("Pilih tanggal start & end dulu!");
                     return;
                 }

                 fetchDashboard({
                     filter: "custom",
                     start: start,
                     end: end
                 });
             });

             // auto-load default
             fetchDashboard({
                 filter: timeFilter.value || "day"
             });
         });
     </script>

 @endsection
