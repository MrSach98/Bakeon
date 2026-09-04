@extends('admin.layouts.app')
@section('title', 'Add Page')
@section('content')
<h4 class="mb-4">Add Page</h4>
<form method="POST" action="{{ route('admin.pages.store') }}">
    @include('admin.pages._form')
</form>
@endsection