@extends('admin.layouts.app')
@section('title', 'Edit Product')

@section('content')
<h4 class="mb-4">Edit Product</h4>

{{-- ✅ Data prepare karo pehle, phir @json mein daalo --}}
@if(isset($product))
    @php
        $variantData = $product->weightVariants->map(function ($variant) {
            return [
                'weight_id' => $variant->weight_id,
                'egg_type' => $variant->egg_type,
                'price' => $variant->price,
                'discount_price' => $variant->discount_price,
                'stock' => $variant->stock,
                'is_default' => (bool) $variant->is_default,
            ];
        })->values();
    @endphp

    <script>
        window.existingVariants = @json($variantData);

        window.existingCategorySelection = {
            category_id: {{ $product->category_id ?? 'null' }},
            subcategory_id: {{ $product->subcategory_id ?? 'null' }},
            child_category_id: {{ $product->child_category_id ?? 'null' }}
        };
    </script>
@endif

<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" id="productForm">
    @method('PUT')
    @include('admin.products._form')
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/product-form.js') }}"></script>
@endpush