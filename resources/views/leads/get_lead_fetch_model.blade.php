  @php
      $source_list = \App\Models\LeadSource::pluck('name', 'id');
  @endphp

<form method="get" action="{{ route('leads.fetch_leads') }}">


  <div class="mb-3">
      <label class="form-label">Enter Total Leads</label>
      <input type="text" class="form-control" id="confirmInput" name="total_lead" required>
  </div>

  <!-- Dropdown -->
  <div class="mb-3">
      <label class="form-label">Select Lead Source</label>
      <select class="form-select" id="confirmDropdown" name="source_id" required>
          <option value="">Select Lead Source</option>
          @foreach ($source_list as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
          @endforeach
      </select>
  </div>

   <div class="mt-4">
            <div class="hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-light"
                    data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success" id="addNewLead">Save
                    </button>
            </div>
        </div>
</form>
