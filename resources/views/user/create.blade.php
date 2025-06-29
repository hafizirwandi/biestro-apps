  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  <div class="text-center mb-4">
      <h3 class="mb-2 modal-title">Tambah User</h3>
  </div>

  <form class="row g-3" method="post" action="{{ route('user.store') }}">
      @csrf
      <div class="col-12 col-md-6">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" placeholder="Enter Text" required />
      </div>
      <div class="col-12 col-md-6">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" placeholder="Enter Text" required />
      </div>
      <div class="col-12 col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="example@domain.com" required />
      </div>
      <div class="col-12 col-md-6">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="*****" required />
      </div>
      <div class="col-12 col-md-6">
          <label class="form-label">Role</label>
          <select name="role" class="form-control" required>
              <option value="">-- Pilih --</option>
              @foreach ($role as $r)
                  <option value="{{ $r->name }}">
                      {{ ucwords($r->name) }}</option>
              @endforeach

          </select>
      </div>
      <div class="col-12 col-md-6">
          <label class="form-label">Status</label>
          <select name="status" class="form-control" required>
              <option value="">-- Pilih --</option>
              <option value="0">Pending</option>
              <option value="1">Active</option>
              <option value="2">Inactive</option>
          </select>
      </div>


      <div class="col-12 text-center">
          <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">
              Cancel
          </button>
      </div>
  </form>
