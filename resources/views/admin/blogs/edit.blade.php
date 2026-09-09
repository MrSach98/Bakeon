{{-- edit.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'Edit Blog Post')
@section('content')
<h4 class="mb-4">Edit Blog Post</h4>
<form method="POST" action="{{ route('admin.blogs.update', $blog) }}" enctype="multipart/form-data">
    @method('PUT')
    @include('admin.blogs._form')
</form>
@endsection
@push('scripts')<script>initCKEditor('blogDescriptionEditor');</script>@endpush