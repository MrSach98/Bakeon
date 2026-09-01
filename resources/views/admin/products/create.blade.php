@extends('admin.layouts.app')

@section('title', 'Add Product')

@section('content')
<h4 class="mb-4">Add Product</h4>

<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" id="productForm">
    @include('admin.products._form')
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/product-form.js') }}"></script>
@endpush