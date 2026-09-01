@extends('admin.layouts.app')

@section('title', 'Serviceable Pincodes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Serviceable Pincodes</h4>
        <small class="text-muted">Manage delivery availability by pincode</small>
    </div>
    <div>
        <a href="{{ route('admin.pincodes.sample-template') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-download"></i> Sample Template
        </a>
        <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fa-solid fa-file-import"></i> Import Excel
        </button>
        <a href="{{ route('admin.pincodes.export') }}" class="btn btn-outline-dark">
            <i class="fa-solid fa-file-export"></i> Export Excel
        </a>
        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#pincodeModal" onclick="openAddModal()">
            + Add Pincode
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="pincodeTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Pincode</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Same Day</th>
                    <th>Midnight</th>
                    <th>Express</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="pincodeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="pincodeForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="pincodeModalLabel">Add Pincode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pincode <span class="text-danger">*</span></label>
                        <input type="text" name="pincode" id="pincode" class="form-control" maxlength="10" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" id="city" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" id="state" class="form-control">
                        </div>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input type="checkbox" name="same_day_available" id="same_day_available" class="form-check-input" value="1" checked>
                        <label class="form-check-label">Same Day Delivery Available</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input type="checkbox" name="midnight_available" id="midnight_available" class="form-check-input" value="1">
                        <label class="form-check-label">Midnight Delivery Available</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input type="checkbox" name="express_available" id="express_available" class="form-check-input" value="1">
                        <label class="form-check-label">Express Delivery Available</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Save Pincode</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.pincodes.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Pincodes from Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        Columns expected: <strong>pincode, city, state, same_day_available, midnight_available, express_available, active</strong>
                        (Yes/No values). Download the sample template first if unsure.
                    </p>
                    <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const pincodeModal = new bootstrap.Modal(document.getElementById('pincodeModal'));
    const pincodeForm = document.getElementById('pincodeForm');

    function openAddModal() {
        document.getElementById('pincodeModalLabel').textContent = 'Add Pincode';
        pincodeForm.action = "{{ route('admin.pincodes.store') }}";
        document.getElementById('formMethod').value = 'POST';
        pincodeForm.reset();
    }

    function openEditModal(id) {
        fetch(`/admin/pincodes/${id}/fetch`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('pincodeModalLabel').textContent = 'Edit Pincode';
                pincodeForm.action = `/admin/pincodes/${id}`;
                document.getElementById('formMethod').value = 'PUT';
                document.getElementById('pincode').value = data.pincode;
                document.getElementById('city').value = data.city;
                document.getElementById('state').value = data.state ?? '';
                document.getElementById('same_day_available').checked = data.same_day_available;
                document.getElementById('midnight_available').checked = data.midnight_available;
                document.getElementById('express_available').checked = data.express_available;
                document.getElementById('is_active').checked = data.is_active;
                pincodeModal.show();
            });
    }

    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this pincode?')) {
            document.getElementById(`deleteForm${id}`).submit();
        }
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('status-toggle')) {
            fetch(`/admin/pincodes/${e.target.dataset.id}/toggle-status`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            });
        }
    });

    $(function () {
        $('#pincodeTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.pincodes.data') }}",
            columns: [
                { data: 'pincode', name: 'pincode' },
                { data: 'city', name: 'city' },
                { data: 'state', name: 'state' },
                { data: 'same_day', name: 'same_day', orderable: false, searchable: false },
                { data: 'midnight', name: 'midnight', orderable: false, searchable: false },
                { data: 'express', name: 'express', orderable: false, searchable: false },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
        });
    });
</script>
@endpush