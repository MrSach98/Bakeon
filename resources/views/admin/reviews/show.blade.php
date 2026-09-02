@extends('admin.layouts.app')

@section('title', 'Review Detail')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Review Detail</h4>
    <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary btn-sm">Back to Reviews</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header fw-bold">Review Content</div>
            <div class="card-body">
                <div class="mb-2" style="color:#ffb800; font-size:1.4rem;">
                    {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                    <span class="text-dark fs-6 ms-2">{{ $review->rating }}/5</span>
                </div>

                @if ($review->title)
                    <h5 class="fw-bold">{{ $review->title }}</h5>
                @endif

                @if ($review->comment)
                    <p class="text-muted">{{ $review->comment }}</p>
                @else
                    <p class="text-muted fst-italic">No written comment.</p>
                @endif

                @if ($review->images->count())
                    <div class="d-flex flex-wrap gap-3 mt-3">
                        @foreach ($review->images as $img)
                            <a href="{{ asset($img->image_path) }}" target="_blank">
                                <img src="{{ asset($img->image_path) }}" width="120" height="120" class="rounded border" style="object-fit:cover;">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-bold">Update Status</div>
            <div class="card-body">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success {{ $review->status === 'approved' ? 'disabled' : '' }}" onclick="updateStatus('approved')">
                        <i class="fa-solid fa-check me-1"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger {{ $review->status === 'rejected' ? 'disabled' : '' }}" onclick="updateStatus('rejected')">
                        <i class="fa-solid fa-xmark me-1"></i> Reject
                    </button>
                </div>
                <div class="mt-2">
                    Current Status:
                    <span class="badge bg-{{ ['pending' => 'warning text-dark', 'approved' => 'success', 'rejected' => 'danger'][$review->status] }}">
                        {{ ucfirst($review->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header fw-bold">Product</div>
            <div class="card-body">
                <p class="mb-1 fw-semibold">{{ $review->product->name ?? '—' }}</p>
                @if ($review->product)
                    <a href="{{ route('admin.products.edit', $review->product) }}" class="small">View Product</a>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header fw-bold">Customer</div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="text-muted d-block small">Name</span>
                    {{ $review->user->name ?? '—' }}
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block small">Email</span>
                    {{ $review->user->email ?? '—' }}
                </div>
                <div>
                    <span class="text-muted d-block small">Submitted On</span>
                    {{ $review->created_at->format('d M Y, h:i A') }}
                </div>
            </div>
        </div>

        @if ($review->order_item_id && $review->orderItem)
        <div class="card">
            <div class="card-header fw-bold">Linked Order</div>
            <div class="card-body">
                <p class="mb-1"><strong>Weight:</strong> {{ $review->orderItem->weight_label ?? '—' }}</p>
                <p class="mb-0"><strong>Order ID:</strong> #{{ $review->orderItem->order_id }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateStatus(status) {
        if (!confirm('Are you sure you want to ' + status.replace('ed', '') + ' this review?')) return;

        $.ajax({
            url: "{{ route('admin.reviews.update-status', $review) }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: { status: status },
            success: function (data) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            },
            error: function () {
                showToast('Something went wrong.', 'danger');
            }
        });
    }
</script>
@endpush