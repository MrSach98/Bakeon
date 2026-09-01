@extends('admin.layouts.app')

@section('title', 'Addons')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Addon Management</h4>
        <small class="text-muted">Candles, Greeting Cards, Birthday Cap, Teddy Bear, etc.</small>
    </div>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addonModal" onclick="openAddModal()">
        + Add Addon
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="addonTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="addonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addonForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="addonModalLabel">Add Addon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" id="price" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" id="stock" class="form-control" value="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control">
                        <img id="imagePreview" class="mt-2 rounded d-none" width="60">
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Save Addon</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const addonModal = new bootstrap.Modal(document.getElementById('addonModal'));
    const addonForm = document.getElementById('addonForm');

    function openAddModal() {
        document.getElementById('addonModalLabel').textContent = 'Add Addon';
        addonForm.action = "{{ route('admin.addons.store') }}";
        document.getElementById('formMethod').value = 'POST';
        addonForm.reset();
        document.getElementById('imagePreview').classList.add('d-none');
    }

    function openEditModal(id) {
        fetch(`/admin/addons/${id}/fetch`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('addonModalLabel').textContent = 'Edit Addon';
                addonForm.action = `/admin/addons/${id}`;
                document.getElementById('formMethod').value = 'PUT';
                document.getElementById('name').value = data.name;
                document.getElementById('price').value = data.price;
                document.getElementById('stock').value = data.stock;
                document.getElementById('is_active').checked = data.is_active;

                const preview = document.getElementById('imagePreview');
                if (data.image_url) {
                    preview.src = data.image_url;
                    preview.classList.remove('d-none');
                } else {
                    preview.classList.add('d-none');
                }

                addonModal.show();
            });
    }

    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this addon?')) {
            document.getElementById(`deleteForm${id}`).submit();
        }
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('status-toggle')) {
            fetch(`/admin/addons/${e.target.dataset.id}/toggle-status`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            });
        }
    });

    $(function () {
        $('#addonTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.addons.data') }}",
            columns: [
                { data: 'image', name: 'image', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'price', name: 'price' },
                { data: 'stock', name: 'stock' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
        });
    });
</script>
@endpush