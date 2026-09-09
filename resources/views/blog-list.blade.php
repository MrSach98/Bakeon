@include('userheader')

<div class="container-fluid px-4 px-lg-5 py-5">
    <h1 class="mb-4" style="font-size:1.8rem;">Our Blog</h1>

    <div class="row g-4">
        @foreach ($blogs as $blog)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    @if ($blog->image)
                        <img src="{{ asset($blog->image) }}" class="card-img-top" style="height:200px; object-fit:cover;">
                    @endif
                    <div class="card-body">
                        <h5 class="fw-bold">{{ $blog->name }}</h5>
                        <p class="text-muted small">{{ Str::limit($blog->short_description, 100) }}</p>
                        <a href="{{ url('/blog/' . $blog->slug) }}" class="btn btn-outline-danger btn-sm">Read More</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $blogs->links() }}</div>
</div>

@include('userfooter')