  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  <div class="text-center mb-4">
      <h3 class="mb-2 modal-title">Add Counter</h3>
  </div>

  <form class="row g-3" method="post" action="{{ route('counter.store') }}">
      @csrf
      <div class="col-12 col-md-12">
          <label class="form-label">Location</label>
          <input type="text" name="location" class="form-control" placeholder="Enter Text" required />
      </div>
      <div class="col-12 col-md-12">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" placeholder="Enter Text" required />
      </div>
      <div class="col-12 col-md-12">
          <label class="form-label">Description</label>
          <textarea name="description" rows="5" class="form-control" placeholder="Enter Text"></textarea>
      </div>
      <div class="col-12 col-md-12">
          <label class="form-label">Is Active</label>
          <select name="is_active" class="form-select">
              <option value="1">Yes</option>
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
