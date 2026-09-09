@extends('admin.layouts.app')

@section('title', 'Newsletter Subscribers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Newsletter Subscribers</h4>
        <small class="text-muted">Everyone who subscribed via the footer signup form</small>
    </div>
    <a href="{{ route('admin.newsletter.export') }}" class="btn btn-outline-dark">
        <i class="fa-solid fa-file-export me-1"></i> Export CSV
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="subscriberTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Email</th>
                    <th>Subscribed On</th>
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
        $('#subscriberTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.newsletter.data') }}",
            columns: [
                { data: 'email', name: 'email' },
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
            order: [[1, 'desc']],
        });
    });
</script>
@endpush