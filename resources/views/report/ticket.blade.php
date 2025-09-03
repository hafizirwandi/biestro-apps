 @extends('layouts.main-layout.app')
 @section('title', 'Report Transaction')

 @section('content')


     <div class="alert alert-primary" role="alert">
         <form action="">
             <div class="row g-3">
                 <div class="col-md-2">
                     <label>Periode</label>
                     <input type="text" id="bs-rangepicker-basic" name="period" value="{{ request()->period }}"
                         class="form-control">
                 </div>
                 <div class="col-md-2">
                     <label>Wahana</label>
                     <select name="wahana" class="form-control">
                         <option value="all" {{ request()->wahana == 'all' ? 'selected' : '' }}>All</option>
                         @foreach ($wahana as $r)
                             <option value="{{ $r->id }}" {{ request()->wahana == $r->id ? 'selected' : '' }}>
                                 {{ $r->name }}</option>
                         @endforeach
                     </select>

                 </div>

                 <div class="col-md-2">
                     <label>Status</label>
                     <select name="status" class="form-control">
                         <option value="all" {{ request()->status == 'all' ? 'selected' : '' }}>All</option>
                         <option value="sold" {{ request()->status == 'sold' ? 'selected' : '' }}>Sold</option>
                         <option value="free" {{ request()->status == 'free' ? 'selected' : '' }}>Free</option>

                     </select>

                 </div>

                 <div class="col-md-1">
                     <button type="submit" class="mt-4 btn btn-warning  text-nowrap  btn-sm waves-effect waves-light">
                         <i class="ti ti-search ti-sm me-2"></i>Cari
                     </button>
                 </div>
             </div>
         </form>
     </div>
     <div class="card mb-4">
         <div class="card-body">

             <div class="table-responsive">

                 <table class="datatable table">
                     <thead>
                         <tr>
                             <th>Transaction code</th>
                             <th>Ticket Code</th>
                             <th>Wahana</th>
                             <th>Status</th>


                         </tr>
                     </thead>

                     <tbody>
                         @foreach ($data as $r)
                             <tr>
                                 <td>{{ $r->transaction->transaction_code }}</td>
                                 <td>{{ $r->ticket_code }}</td>
                                 <td>{{ $r->wahana?->name }}</td>
                                 <td>{{ $r->free_gift_rule_id != null ? 'Free' : 'Sold' }}</td>

                             </tr>
                         @endforeach
                     </tbody>

                 </table>
             </div>
         </div>
     </div>

 @endsection
