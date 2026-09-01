@extends('admin.layouts.app')

@section('title', 'Flavors')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Flavor Management</h4>
        <small class="text-muted">Chocolate, Vanilla, Red Velvet, Black Forest, etc.</small>
    </div>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#flavorModal" onclick="openAddModal()">
        + Add Flavor
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="flavorTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Swatch</th>
                    <th>Name</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="flavorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="flavorForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="flavorModalLabel">Add Flavor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Swatch Color (optional)</label>
                        <input type="color" name="swatch_color" id="swatch_color" class="form-control form-control-color" value="#8B4513">
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Save Flavor</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const flavorModal = new bootstrap.Modal(document.getElementById('flavorModal'));
    const flavorForm = document.getElementById('flavorForm');

    function openAddModal() {
        document.getElementById('flavorModalLabel').textContent = 'Add Flavor';
        flavorForm.action = "{{ route('admin.flavors.store') }}";
        document.getElementById('formMethod').value = 'POST';
        flavorForm.reset();
    }

    function openEditModal(id) {
        fetch(`/admin/flavors/${id}/fetch`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('flavorModalLabel').textContent = 'Edit Flavor';
                flavorForm.action = `/admin/flavors/${id}`;
                document.getElementById('formMethod').value = 'PUT';
                document.getElementById('name').value = data.name;
                document.getElementById('swatch_color').value = data.swatch_color ?? '#8B4513';
                document.getElementById('is_active').checked = data.is_active;
                flavorModal.show();
            });
    }

    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this flavor?')) {
            document.getElementById(`deleteForm${id}`).submit();
        }
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('status-toggle')) {
            fetch(`/admin/flavors/${e.target.dataset.id}/toggle-status`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            });
        }
    });

    $(function () {
        $('#flavorTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.flavors.data') }}",
            columns: [
                { data: 'swatch', name: 'swatch', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'products_count', name: 'products_count', orderable: false, searchable: false },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
        });
    });
</script>
@endpush