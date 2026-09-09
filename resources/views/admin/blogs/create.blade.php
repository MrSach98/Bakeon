{{-- create.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'Add Blog Post')
@section('content')
<h4 class="mb-4">Add Blog Post</h4>
<form method="POST" action="{{ route('admin.blogs.store') }}" enctype="multipart/form-data">
    @include('admin.blogs._form')
</form>
@endsection
@push('scripts')<script>initCKEditor('blogDescriptionEditor');</script>@endpush