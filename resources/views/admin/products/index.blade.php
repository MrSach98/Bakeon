@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Product Management</h4>
        <small class="text-muted">Manage all cakes and their variants</small>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-dark">+ Add Product</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="productTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Base Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this product?')) {
            document.getElementById(`deleteForm${id}`).submit();
        }
    }

    $(function () {
        $('#productTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.products.data') }}",
            columns: [
                { data: 'image', name: 'image', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'category_name', name: 'category_name', orderable: false, searchable: false },
                { data: 'base_price', name: 'base_price' },
                { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
        });
    });
</script>
@endpush