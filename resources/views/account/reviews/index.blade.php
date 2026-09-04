@include('userheader')

<div class="container-fluid px-4 px-lg-5 py-4">
    <h1 class="mb-4" style="font-size:1.6rem;">My Account</h1>

    <div class="row">
        @include('account.partials.sidebar')

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">My Reviews</h5>

                    @if ($reviews->count())
                        @foreach ($reviews as $review)
                            <div class="border rounded-3 p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-bold">{{ $review->product->name ?? 'Product' }}</div>
                                        <div style="color:#ffb800;">
                                            {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                        </div>
                                    </div>
                                    <span class="badge bg-{{ ['pending' => 'warning text-dark', 'approved' => 'success', 'rejected' => 'danger'][$review->status] }}">
                                        {{ ucfirst($review->status) }}
                                    </span>
                                </div>

                                @if ($review->title)<div class="fw-semibold mt-2">{{ $review->title }}</div>@endif
                                @if ($review->comment)<p class="small text-muted mb-2">{{ $review->comment }}</p>@endif

                                @if ($review->images->count())
                                    <div class="d-flex gap-2">
                                        @foreach ($review->images as $img)
                                            <img src="{{ asset($img->image_path) }}" width="50" height="50" class="rounded" style="object-fit:cover;">
                                        @endforeach
                                    </div>
                                @endif

                                <small class="text-muted d-block mt-2">{{ $review->created_at->format('d M Y') }}</small>
                            </div>
                        @endforeach

                        <div class="mt-3">{{ $reviews->links() }}</div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa-regular fa-star fa-3x text-muted mb-3"></i>
                            <p class="text-muted">You haven't written any reviews yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('userfooter')