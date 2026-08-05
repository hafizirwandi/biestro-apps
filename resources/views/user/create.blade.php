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
          <select name="role" id="roleSelect" class="form-control" required>
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

      <div class="col-12 col-md-6">
          <label class="form-label">SPV PIN</label>
          <input type="password" name="spv_pin" class="form-control" placeholder="PIN Password (4-6 digit)"
              minlength="4" maxlength="6" />
          <small class="text-muted">Isi jika role Supervisor</small>
      </div>

      <div class="col-12 col-md-6 d-none" id="wahanaWrapper">
          <label class="form-label">Wahana yang Boleh Discan</label>
          <select name="wahana_ids[]" class="select2 form-control" multiple>
              @foreach ($wahanas as $w)
                  <option value="{{ $w->id }}">{{ $w->name }}</option>
              @endforeach
          </select>
      </div>

      <div class="col-12">
          <div class="form-check">
              <input class="form-check-input" type="checkbox" name="scan_unflag_authorized" value="1"
                  id="scanUnflagCreate">
              <label class="form-check-label" for="scanUnflagCreate">
                  Otorisasi Unflag Tiket (bisa membatalkan status "sudah digunakan" via SPV PIN)
              </label>
          </div>
      </div>


      <div class="col-12 text-center">
          <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">
              Cancel
          </button>
      </div>
  </form>
  <script>
      (function() {
          $('#wahanaWrapper .select2').select2({
              dropdownParent: $('#myModal')
          });

          function toggleWahana() {
              $('#wahanaWrapper').toggleClass('d-none', $('#roleSelect').val() !== 'scan');
          }
          $('#roleSelect').off('change.wahanaToggle').on('change.wahanaToggle', toggleWahana);
          toggleWahana();
      })();
  </script>
