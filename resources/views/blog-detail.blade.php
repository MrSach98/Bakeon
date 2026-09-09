@include('userheader')

<div class="container-fluid px-4 px-lg-5 py-5">
    <div style="max-width:800px; margin:0 auto;">
        <nav class="mb-3">
            <small class="text-muted"><a href="{{ url('/blog') }}" class="text-decoration-none text-muted">Blog</a> / {{ $blog->name }}</small>
        </nav>

        <h1 class="mb-3" style="font-size:1.8rem;">{{ $blog->name }}</h1>

        @if ($blog->image)
            <img src="{{ asset($blog->image) }}" class="w-100 rounded-3 mb-4" style="max-height:400px; object-fit:cover;">
        @endif

        <div class="blog-content">{!! $blog->description !!}</div>
    </div>

    @if ($relatedBlogs->count())
        <div style="max-width:800px; margin:40px auto 0;">
            <h5 class="fw-bold mb-3">More Posts</h5>
            <div class="row g-3">
                @foreach ($relatedBlogs as $related)
                    <div class="col-md-6">
                        <a href="{{ url('/blog/' . $related->slug) }}" class="text-decoration-none text-dark">
                            <strong>{{ $related->name }}</strong>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@include('userfooter')