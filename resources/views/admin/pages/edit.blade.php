@extends('admin.layouts.app')
@section('title', 'Edit Page')
@section('content')
<h4 class="mb-4">Edit Page</h4>
<form method="POST" action="{{ route('admin.pages.update', $page) }}">
    @method('PUT')
    @include('admin.pages._form')
</form>
@endsection