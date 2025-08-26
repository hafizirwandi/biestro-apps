 @extends('layouts.main-layout.app-without-menu')
 @section('title', 'Ticket')
 @section('content')
     <div class="d-flex justify-content-center align-items-center" style="height: 70vh;">
         <div class="col-md-4">
             <<div class="alert alert-success" role="alert">
                 <h4 class="alert-heading">Are you sure?</h4>
                 <p>
                     Before you can start processing any transactions, you need to open a new shift.
                     Opening a shift will start a new session for today, allowing you to record all activities,
                     track cash flow, and manage your transactions properly.
                     Please make sure you are ready to begin before proceeding.
                 </p>
         </div>

         <div class="card">
             <div class="card-body">
                 <h3 class="text-center">Open Shift</h3>
                 <form class="row g-3" method="post" action="{{ route('transaction.set-open-shift') }}">
                     @csrf
                     <div class="col-12 col-md-12">
                         <label class="form-label">Counter</label>
                         <select name="counter_id" class="form-select" placeholder="Enter Text" required>
                             @foreach ($counter as $r)
                                 <option value="{{ $r->id }}">{{ $r->name }}</option>
                             @endforeach

                         </select>
                     </div>
                     <div class="col-12 col-md-12">
                         <label class="form-label">Opening Balance</label>
                         <input type="text" name="opening_balance" class="form-control auto-numeric"
                             placeholder="Enter Text" required />
                     </div>

                     <div class="col-12 text-center">
                         <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                     </div>
                 </form>
             </div>
         </div>

     </div>
     </div>
 @endsection
