@extends('admin.layouts.app')

@section('title', 'Pages')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Content Pages</h4>
        <small class="text-muted">About Us, Terms &amp; Conditions, Privacy Policy, FAQ, etc.</small>
    </div>
    <a href="{{ route('admin.pages.create') }}" class="btn btn-dark">+ Add Page</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="pageTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
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
        $('#pageTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.pages.data') }}",
            columns: [
                { data: 'title', name: 'title' },
                { data: 'slug', name: 'slug' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
        });
    });

    $(document).on('change', '.status-toggle', function () {
        fetch(`/admin/pages/${this.dataset.id}/toggle-status`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        });
    });
</script>
@endpush