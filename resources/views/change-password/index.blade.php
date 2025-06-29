 @extends('layouts.main-layout.app')
 @section('title', 'Ganti Password')
 @section('content')
     <div class="row">
         <div class="col-md-6">
             <div class="card mb-4">
                 <div class="card-body">
                     <form class="row g-3" method="post" action="{{ route('save-new-password') }}">
                         @csrf
                         <div class="col-12 col-md-12">
                             <label class="form-label">Current Password</label>
                             <input type="password" name="current_password" class="form-control" placeholder="Enter Text"
                                 required />
                         </div>
                         <div class="col-12 col-md-12">
                             <label class="form-label">New Password</label>
                             <input type="password" name="new_password" class="form-control" placeholder="Enter Text"
                                 required />
                         </div>
                         <div class="col-12 col-md-12">
                             <label class="form-label">Confirm Password</label>
                             <input type="password" name="confirm_password" class="form-control" placeholder="Enter Text"
                                 required />
                         </div>


                         <div class="col-12 text-center">
                             <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                             <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                 aria-label="Close">
                                 Cancel
                             </button>
                         </div>
                     </form>

                 </div>
             </div>
         </div>
     </div>

 @endsection
