  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  <div class="text-center mb-4">
      <h3 class="mb-2 modal-title">Tambah Survey</h3>
  </div>

  <form class="row g-3" method="post" action="{{ route('survey.store') }}">
      @csrf
      <div class="col-12 col-md-12">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" placeholder="Enter Text" required />
      </div>
      <div class="col-12 col-md-6">
          <label class="form-label">Start at</label>
          <input type="datetime-local" name="start_at" class="form-control" placeholder="Enter Text" required />
      </div>
      <div class="col-12 col-md-6">
          <label class="form-label">End at</label>
          <input type="datetime-local" name="end_at" class="form-control" placeholder="Enter Text" required />
      </div>
      <div class="col-12 col-md-12">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" placeholder="Enter Text" required></textarea>
      </div>

      <div class="col-12 text-center">
          <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">
              Cancel
          </button>
      </div>
  </form>
