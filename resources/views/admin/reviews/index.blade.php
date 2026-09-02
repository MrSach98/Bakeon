@extends('admin.layouts.app')

@section('title', 'Reviews')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Reviews &amp; Ratings</h4>
        <small class="text-muted">Moderate customer reviews before they go live</small>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="clearFiltersBtn">Clear Filters</button>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="reviewTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Photos</th>
                    <th>Submitted</th>
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
        const table = $('#reviewTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.reviews.data') }}",
                data: function (d) {
                    d.status = $('#filterStatus').val();
                }
            },
            columns: [
                { data: 'product_name', name: 'product_name' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'rating_stars', name: 'rating_stars', orderable: false, searchable: false },
                { data: 'has_images', name: 'has_images', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
            order: [[4, 'desc']],
        });

        $('#filterStatus').on('change', function () {
            table.ajax.reload();
        });

        $('#clearFiltersBtn').on('click', function () {
            $('#filterStatus').val('');
            table.ajax.reload();
        });
    });

    function updateReviewStatus(reviewId, status) {
        if (!confirm('Are you sure you want to ' + status.replace('ed', '') + ' this review?')) return;

        $.ajax({
            url: `/admin/reviews/${reviewId}/update-status`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: { status: status },
            success: function (data) {
                showToast(data.message, 'success');
                $('#reviewTable').DataTable().ajax.reload(null, false);
            },
            error: function () {
                showToast('Something went wrong.', 'danger');
            }
        });
    }
</script>
@endpush