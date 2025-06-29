  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  <div class="text-center mb-4">
      <h3 class="mb-2 modal-title">Edit Wahana</h3>
  </div>
  <form class="row g-3" method="post" action="{{ route('wahana.update', $data->id) }}">
      @csrf
      @method('PUT')
      <div class="col-12 col-md-12">
          <label class="form-label">Key</label>
          <input type="text" name="key" class="form-control" value="{{ $data->key }}" placeholder="Enter Text"
              required />
      </div>
      <div class="col-12 col-md-12">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" value="{{ $data->name }}" placeholder="Enter Text"
              required />
      </div>
      <div class="col-12 col-md-12">
          <label class="form-label">Description</label>
          <textarea name="description" rows="5" class="form-control" placeholder="Enter Text">{{ $data->description }}</textarea>
      </div>
      <div class="col-12 text-center">
          <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">
              Cancel
          </button>
      </div>
  </form>
