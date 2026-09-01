@extends('admin.layouts.app')

@section('title', 'Occasions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Occasion Management</h4>
        <small class="text-muted">Birthday, Anniversary, Valentine's Day, Wedding, etc.</small>
    </div>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#occasionModal" onclick="openAddModal()">
        + Add Occasion
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="occasionTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="occasionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="occasionForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="occasionModalLabel">Add Occasion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Save Occasion</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const occasionModal = new bootstrap.Modal(document.getElementById('occasionModal'));
    const occasionForm = document.getElementById('occasionForm');

    function openAddModal() {
        document.getElementById('occasionModalLabel').textContent = 'Add Occasion';
        occasionForm.action = "{{ route('admin.occasions.store') }}";
        document.getElementById('formMethod').value = 'POST';
        occasionForm.reset();
    }

    function openEditModal(id) {
        fetch(`/admin/occasions/${id}/fetch`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('occasionModalLabel').textContent = 'Edit Occasion';
                occasionForm.action = `/admin/occasions/${id}`;
                document.getElementById('formMethod').value = 'PUT';
                document.getElementById('name').value = data.name;
                document.getElementById('is_active').checked = data.is_active;
                occasionModal.show();
            });
    }

    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this occasion?')) {
            document.getElementById(`deleteForm${id}`).submit();
        }
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('status-toggle')) {
            fetch(`/admin/occasions/${e.target.dataset.id}/toggle-status`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            });
        }
    });

    $(function () {
        $('#occasionTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.occasions.data') }}",
            columns: [
                { data: 'name', name: 'name' },
                { data: 'products_count', name: 'products_count', orderable: false, searchable: false },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
        });
    });
</script>
@endpush