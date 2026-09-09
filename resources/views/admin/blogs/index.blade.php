@extends('admin.layouts.app')

@section('title', 'Blog Posts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Blog Posts</h4>
        <small class="text-muted">Manage your blog content</small>
    </div>
    <a href="{{ route('admin.blogs.create') }}" class="btn btn-dark">+ Add Blog Post</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="blogTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Slug</th>
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
    $(function () {
        $('#blogTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.blogs.data') }}",
            columns: [
                { data: 'image', name: 'image', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'slug', name: 'slug' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
        });
    });

    $(document).on('change', '.status-toggle', function () {
        fetch(`/admin/blogs/${this.dataset.id}/toggle-status`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        });
    });
</script>
@endpush