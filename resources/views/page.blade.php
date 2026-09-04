@include('userheader')

<div class="container-fluid px-4 px-lg-5 py-5">
    <div style="max-width:800px; margin:0 auto;">
        <nav class="mb-3">
            <small class="text-muted">
                <a href="{{ url('/') }}" class="text-decoration-none text-muted">Home</a> /
                <span class="text-dark fw-semibold">{{ $page->title }}</span>
            </small>
        </nav>

        <h1 class="mb-4" style="font-size:1.8rem;">{{ $page->title }}</h1>

        <div class="page-content">
            {!! $page->content !!}
        </div>
    </div>
</div>

@include('userfooter')