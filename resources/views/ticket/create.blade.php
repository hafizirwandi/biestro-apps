  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  <div class="text-center mb-4">
      <h3 class="mb-2 modal-title">Add Ticket</h3>
  </div>

  <form class="row g-3" method="post" action="{{ route('ticket.store') }}">
      @csrf
      <div class="col-12 col-md-12">
          <label class="form-label">Wahana</label>
          <select name="wahana_id" class="form-select" placeholder="Enter Text" required>
              <option value="">- choose -</option>
              @foreach ($wahana as $r)
                  <option value="{{ $r->id }}">{{ $r->name }}</option>
              @endforeach
          </select>
      </div>
      <div class="col-12 col-md-12">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" placeholder="Enter Text" required />
      </div>
      <div class="col-12 col-md-12">
          <label class="form-label">Price</label>
          <input type="text" name="price" class="form-control auto-numeric" placeholder="Enter Text" required />
      </div>
      <div class="col-12 col-md-12">
          <label class="form-label">Is Active</label>
          <select name="is_active" class="form-control" placeholder="Enter Text" required>
              <option value="1">Ya</option>
              <option value="0">No</option>
          </select>
      </div>

      <div class="col-12 text-center">
          <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">
              Cancel
          </button>
      </div>
  </form>
