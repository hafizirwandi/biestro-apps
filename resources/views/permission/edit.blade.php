  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  <div class="text-center mb-4">
      <h3 class="mb-2 modal-title">Edit Permission</h3>
  </div>
  <form class="row g-3" method="post" action="{{ route('permission.update', $data->id) }}">
      @csrf
      @method('PUT')
      <input type="hidden" name="guard_name" value="web">
      <div class="col-12 col-md-12">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" placeholder="Enter Text" value="{{ $data->name }}"
              required />
      </div>

      <div class="col-12 text-center">
          <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">
              Cancel
          </button>
      </div>
  </form>
