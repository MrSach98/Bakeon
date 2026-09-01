@extends('admin.layouts.app')

@section('title', 'Weights')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Weight Management</h4>
        <small class="text-muted">0.5 Kg, 1 Kg, 1.5 Kg, 2 Kg, 3 Kg... used for pricing variants</small>
    </div>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#weightModal" onclick="openAddModal()">
        + Add Weight
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="weightTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Label</th>
                    <th>Value (Kg)</th>
                    <th>Serves (approx)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="weightModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="weightForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="weightModalLabel">Add Weight</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Label <span class="text-danger">*</span></label>
                        <input type="text" name="label" id="label" class="form-control" placeholder="e.g. 1 Kg" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Value in Kg <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="value_kg" id="value_kg" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Approx Serves</label>
                        <input type="number" name="serves" id="serves" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control" value="0">
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Save Weight</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const weightModal = new bootstrap.Modal(document.getElementById('weightModal'));
    const weightForm = document.getElementById('weightForm');

    function openAddModal() {
        document.getElementById('weightModalLabel').textContent = 'Add Weight';
        weightForm.action = "{{ route('admin.weights.store') }}";
        document.getElementById('formMethod').value = 'POST';
        weightForm.reset();
    }

    function openEditModal(id) {
        fetch(`/admin/weights/${id}/fetch`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('weightModalLabel').textContent = 'Edit Weight';
                weightForm.action = `/admin/weights/${id}`;
                document.getElementById('formMethod').value = 'PUT';
                document.getElementById('label').value = data.label;
                document.getElementById('value_kg').value = data.value_kg;
                document.getElementById('serves').value = data.serves ?? '';
                document.getElementById('sort_order').value = data.sort_order;
                document.getElementById('is_active').checked = data.is_active;
                weightModal.show();
            });
    }

    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this weight?')) {
            document.getElementById(`deleteForm${id}`).submit();
        }
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('status-toggle')) {
            fetch(`/admin/weights/${e.target.dataset.id}/toggle-status`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            });
        }
    });

    $(function () {
        $('#weightTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.weights.data') }}",
            columns: [
                { data: 'label', name: 'label' },
                { data: 'value_kg', name: 'value_kg' },
                { data: 'serves', name: 'serves' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
        });
    });
</script>
@endpush