 @extends('layouts.main-layout.app')
 @section('title', 'Create Ticket package')
 @section('content')

     <form class="row g-3" method="post" action="{{ route('ticket-package.store') }}">
         @csrf

         <div class="row">
             <div class="col-md-6">
                 <div class="card mb-4">
                     <div class="card-body">
                         <div class="col-12 col-md-12 mb-3">
                             <label class="form-label">Name</label>
                             <input type="text" name="name" class="form-control" placeholder="Enter Text" required />
                         </div>
                         <div class="row">
                             <div class="col-6 col-md-6 mb-3">
                                 <label class="form-label">Price</label>
                                 <input type="text" name="price" class="form-control auto-numeric"
                                     placeholder="Enter Text" required />
                             </div>
                             <div class="col-6 col-md-6 mb-3">
                                 <label class="form-label">Is Active</label>
                                 <select name="is_active" class="form-select" placeholder="Enter Text" required>
                                     <option value="1">Active</option>
                                     <option value="0">Not Active</option>
                                 </select>
                             </div>
                         </div>
                         <div class="col-12 col-md-12 mb-3">
                             <label class="form-label">Description</label>
                             <textarea name="description" rows="5" class="form-control" placeholder="Enter Text"></textarea>
                         </div>
                         <div class="col-12 ">
                             <button type="submit" id="btnSubmit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                         </div>
                     </div>
                 </div>
             </div>
             <div class="col-md-6">
                 <button class="btn btn-secondary mb-3" id="addWahana">Add Wahana</button>
                 <div id="listWahana" style="padding: 0"></div>
             </div>
         </div>
     </form>

 @endsection
 @section('script')

     <script>
         const addWahana = document.getElementById('addWahana');
         const listWahana = document.getElementById("listWahana");
         const btnSubmit = document.getElementById("btnSubmit");


         const wahanaList = [];
         async function loadWahanaData() {
             try {
                 const response = await fetch("{{ route('wahana.get-data') }}");
                 const data = await response.json();
                 wahanaList.push(...data.data);
             } catch (error) {
                 console.error("Gagal ambil data wahana:", error);
             }
         }




         // Fungsi untuk membuat 1 baris input wahana + qty + tombol hapus
         function createWahanaRow() {
             const div = document.createElement("div");
             div.className = "d-flex gap-2 p-0 mb-3 align-items-center list-wahana";

             // Buat select
             const select = document.createElement("select");
             select.required = true;
             select.name = "wahana_id[]";
             select.className = "form-select";

             // Tambahkan option default
             const defaultOption = document.createElement("option");
             defaultOption.value = "";
             defaultOption.textContent = "Choose Wahana";
             select.appendChild(defaultOption);

             // Isi <option> dari wahanaList
             wahanaList.forEach(wahana => {
                 const option = document.createElement("option");
                 option.value = wahana.id;
                 option.textContent = wahana.name;
                 select.appendChild(option);
             });

             // Buat input qty
             const inputQty = document.createElement("input");
             inputQty.type = "number";
             inputQty.name = "qty[]";
             inputQty.className = "form-control";
             inputQty.style.width = "70px";
             inputQty.value = 1;

             // Tombol hapus
             const btnDelete = document.createElement("a");
             btnDelete.href = "javascript:;";
             btnDelete.className = "btn btn-danger btn-remove-wahana";
             btnDelete.innerHTML = `<i class="ti ti-trash ti-sm"></i>`;
             btnDelete.addEventListener('click', function(e) {
                 e.target.closest(".list-wahana").remove();
             });

             // Gabungkan semua ke div
             div.appendChild(select);
             div.appendChild(inputQty);
             div.appendChild(btnDelete);

             return div;
         }

         addWahana.addEventListener("click", function(e) {
             e.preventDefault();
             const row = createWahanaRow();
             listWahana.appendChild(row);
         })

         btnSubmit.addEventListener("click", function(e) {
             const wahanaSelects = document.querySelectorAll('select[name="wahana_id[]"]');
             const qtyInputs = document.querySelectorAll('input[name="qty[]"]');
             const name = document.querySelectorAll('input[name="name"]');
             const price = document.querySelectorAll('input[name="price"]');
             const isActive = document.querySelectorAll('select[name="is_active"]');
             const description = document.querySelectorAll('textarea[name="description"]');


             let isValid = true;
             let selectedValues = new Set();



             if (wahanaSelects.length === 0) {
                 alert("Pilih wahana terlebih dahulu");
                 isValid = false;
             }

             for (let i = 0; i < wahanaSelects.length; i++) {
                 const selectedValue = wahanaSelects[i].value;
                 const qtyValue = qtyInputs[i]?.value;


                 if (!selectedValue || !qtyValue || qtyValue <= 0) {
                     alert("Pastikan semua wahana dan quantity telah diisi dengan benar.");
                     isValid = false;
                     break;
                 }


                 if (selectedValues.has(selectedValue)) {
                     alert("Terdapat wahana yang dipilih lebih dari sekali. Harap pilih berbeda.");
                     isValid = false;
                     break;
                 }

                 selectedValues.add(selectedValue);
             }


             if (!isValid) {
                 e.preventDefault(); // cegah submit form
             }
         })

         loadWahanaData();
     </script>


 @endsection
