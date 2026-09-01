@csrf

<ul class="nav nav-tabs mb-3" id="productTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-basic" type="button">Basic Info</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-category" type="button">Category</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-pricing" type="button">Pricing</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-variants" type="button">Weight Variants</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-attributes" type="button">Flavors / Addons / Occasions / Delivery</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-images" type="button">Images</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-customization" type="button">Customization</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-seo" type="button">SEO</button>
    </li>
</ul>

<div class="tab-content">

    <!-- Basic Info -->
    <div class="tab-pane fade show active" id="tab-basic">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" data-required="true">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku ?? '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach (['draft' => 'Draft', 'active' => 'Active', 'inactive' => 'Inactive'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $product->status ?? 'draft') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Short Description</label>
                    <textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $product->short_description ?? '') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Full Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $product->description ?? '') }}</textarea>
                </div>

                <div class="form-check form-switch mb-2">
                    <input type="checkbox" name="is_featured" class="form-check-input" value="1"
                           {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label">Featured</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" name="is_bestseller" class="form-check-input" value="1"
                           {{ old('is_bestseller', $product->is_bestseller ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label">Bestseller</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Category -->
    <div class="tab-pane fade" id="tab-category">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select" data-required="true">
                            <option value="">-- Select --</option>
                            @foreach ($topLevelCategories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Subcategory (optional)</label>
                        <select name="subcategory_id" id="subcategory_id" class="form-select">
                            <option value="">-- None --</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Child Category (optional)</label>
                        <select name="child_category_id" id="child_category_id" class="form-select">
                            <option value="">-- None --</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing -->
    <div class="tab-pane fade" id="tab-pricing">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Base Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="base_price" class="form-control" value="{{ old('base_price', $product->base_price ?? '') }}" data-required="true">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Discount Price (₹)</label>
                        <input type="number" step="0.01" name="discount_price" class="form-control" value="{{ old('discount_price', $product->discount_price ?? '') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Egg Type <span class="text-danger">*</span></label>
                        <select name="egg_type" class="form-select">
                            @foreach (['eggless' => 'Eggless', 'egg' => 'With Egg', 'both' => 'Both Available'] as $val => $label)
                                <option value="{{ $val }}" {{ old('egg_type', $product->egg_type ?? 'eggless') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weight Variants -->
    <div class="tab-pane fade" id="tab-variants">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                Weight Variants
                <button type="button" class="btn btn-sm btn-dark" onclick="addVariantRow()">+ Add Variant</button>
            </div>
            <div class="card-body">
                <table class="table table-sm align-middle" id="variantTable">
                    <thead>
                        <tr>
                            <th>Default</th>
                            <th>Weight</th>
                            <th>Egg Type</th>
                            <th>Price (₹)</th>
                            <th>Discount Price</th>
                            <th>Stock</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="variantRows"></tbody>
                </table>
                <div class="form-text">Add at least one weight variant — this is the actual price/stock a customer buys.</div>
            </div>
        </div>
    </div>

    <!-- Flavors / Addons / Occasions / Delivery -->
    <div class="tab-pane fade" id="tab-attributes">
        <div class="card mb-3">
            <div class="card-header">Flavors</div>
            <div class="card-body">
                <div class="row">
                    @foreach ($flavors as $flavor)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="flavor_ids[]" value="{{ $flavor->id }}"
                                       class="form-check-input" id="flavor_{{ $flavor->id }}"
                                       {{ in_array($flavor->id, old('flavor_ids', $product->flavors->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="flavor_{{ $flavor->id }}">{{ $flavor->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Addons</div>
            <div class="card-body">
                <div class="row">
                    @foreach ($addons as $addon)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}"
                                       class="form-check-input" id="addon_{{ $addon->id }}"
                                       {{ in_array($addon->id, old('addon_ids', $product->addons->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="addon_{{ $addon->id }}">{{ $addon->name }} (₹{{ $addon->price }})</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Occasions</div>
            <div class="card-body">
                <div class="row">
                    @foreach ($occasions as $occasion)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="occasion_ids[]" value="{{ $occasion->id }}"
                                       class="form-check-input" id="occasion_{{ $occasion->id }}"
                                       {{ in_array($occasion->id, old('occasion_ids', $product->occasions->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="occasion_{{ $occasion->id }}">{{ $occasion->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Delivery Options</div>
            <div class="card-body">
                <div class="row">
                    @foreach ($deliveryOptions as $option)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="delivery_option_ids[]" value="{{ $option->id }}"
                                       class="form-check-input" id="delivery_{{ $option->id }}"
                                       {{ in_array($option->id, old('delivery_option_ids', $product->deliveryOptions->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="delivery_{{ $option->id }}">{{ $option->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Images -->
    <div class="tab-pane fade" id="tab-images">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3" id="imagePreviewContainer">
                    @if (! empty($product) && $product->images->count())
                        @foreach ($product->images as $img)
                            <div class="product-image-thumb-wrap" data-image-id="{{ $img->id }}">
                                <img src="{{ asset($img->image_path) }}">
                                <button type="button" class="btn btn-sm btn-danger remove-image-btn" onclick="deleteExistingImage({{ $img->id }}, this)">×</button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <input type="file" name="images[]" id="productImagesInput" class="form-control mt-3" multiple accept="image/*">
                <div class="d-flex flex-wrap gap-3 mt-2" id="newImagePreviewContainer"></div>
                <div class="form-text">You can select multiple images. First one becomes primary if none exists.</div>
            </div>
        </div>
    </div>

    <!-- Customization -->
    <div class="tab-pane fade" id="tab-customization">
        <div class="card">
            <div class="card-body">
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" name="is_photo_cake" class="form-check-input" value="1"
                           {{ old('is_photo_cake', $product->is_photo_cake ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label">Allow Photo Upload (Photo Cake)</label>
                </div>

                <div class="form-check form-switch mb-2">
                    <input type="checkbox" name="is_message_enabled" class="form-check-input" value="1"
                           {{ old('is_message_enabled', $product->is_message_enabled ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label">Allow "Message on Cake"</label>
                </div>

                <div class="mb-2" style="max-width:250px;">
                    <label class="form-label">Message Character Limit</label>
                    <input type="number" name="message_char_limit" class="form-control"
                           value="{{ old('message_char_limit', $product->message_char_limit ?? 30) }}">
                </div>
            </div>
        </div>
    </div>

    <!-- SEO -->
    <!-- SEO -->
    <div class="tab-pane fade" id="tab-seo">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $product->meta_title ?? '') }}" maxlength="255">
                    <div class="form-text">Recommended: 50-60 characters for best search display.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="3" maxlength="300">{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
                    <div class="form-text">Recommended: 150-160 characters for best search display.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $product->meta_keywords ?? '') }}" maxlength="255" placeholder="e.g. chocolate cake, birthday cake, eggless cake">
                    <div class="form-text">Comma-separated keywords.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">URL Slug</label>
                    <input type="text" class="form-control" value="{{ $product->slug ?? '(auto-generated from name)' }}" disabled>
                    <div class="form-text">Slug is generated automatically from the product name.</div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="mt-3">
    <button type="submit" class="btn btn-dark px-4">Save Product</button>
</div>

<!-- Row template for JS to clone -->
<template id="variantRowTemplate">
    <tr>
        <td class="text-center">
            <input type="radio" name="variant_default" class="form-check-input" value="__INDEX__">
        </td>
        <td>
            <select name="variant_weight_id[]" class="form-select form-select-sm" data-required="true">
                <option value="">-- Weight --</option>
                @foreach ($weights as $weight)
                    <option value="{{ $weight->id }}">{{ $weight->label }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="variant_egg_type[]" class="form-select form-select-sm">
                <option value="eggless">Eggless</option>
                <option value="egg">Egg</option>
            </select>
        </td>
        <td><input type="number" step="0.01" name="variant_price[]" class="form-control form-control-sm" data-required="true"></td>
        <td><input type="number" step="0.01" name="variant_discount_price[]" class="form-control form-control-sm"></td>
        <td><input type="number" name="variant_stock[]" class="form-control form-control-sm" value="0"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>
    </tr>
</template>

<script>
    // Prevent switching tabs if the currently active tab has unfilled required fields
    document.addEventListener('DOMContentLoaded', function () {
        const tabButtons = document.querySelectorAll('#productTabs button[data-bs-toggle="tab"]');

        tabButtons.forEach(button => {
            button.addEventListener('show.bs.tab', function (event) {
                const currentActivePane = document.querySelector('#productTabs .nav-link.active')
                    ?.getAttribute('data-bs-target');

                if (!currentActivePane) return;

                const currentPane = document.querySelector(currentActivePane);
                const requiredFields = currentPane.querySelectorAll('[required]');

                for (const field of requiredFields) {
                    if (!field.checkValidity()) {
                        event.preventDefault();
                        field.reportValidity();
                        field.focus();
                        return;
                    }
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
    const tabLinks = document.querySelectorAll('#productTabs button[data-bs-toggle="tab"]');

    function validateTabPane(paneSelector) {
        const pane = document.querySelector(paneSelector);
        if (!pane) return true;

        let allValid = true;

        pane.querySelectorAll('[data-required="true"]').forEach(field => {
            const errorId = field.id ? field.id + '_error' : null;
            const value = field.value ? field.value.trim() : '';

            if (!value) {
                allValid = false;
                field.classList.add('is-invalid');

                // Field ke neeche error dikhao
                let errorEl = field.nextElementSibling;
                if (!errorEl || !errorEl.classList.contains('field-error-msg')) {
                    errorEl = document.createElement('div');
                    errorEl.className = 'field-error-msg text-danger small mt-1';
                    field.insertAdjacentElement('afterend', errorEl);
                }
                errorEl.textContent = 'This field is required.';
            } else {
                field.classList.remove('is-invalid');
                const errorEl = field.nextElementSibling;
                if (errorEl && errorEl.classList.contains('field-error-msg')) {
                    errorEl.remove();
                }
            }
        });

        return allValid;
    }

    tabLinks.forEach(button => {
        button.addEventListener('show.bs.tab', function (event) {
            const currentActivePane = document.querySelector('#productTabs .nav-link.active')
                ?.getAttribute('data-bs-target');

            if (!currentActivePane) return;

            if (!validateTabPane(currentActivePane)) {
                event.preventDefault();
                showToast('Please fill all required fields in this tab before moving forward.', 'danger');
            }
        });
    });

    // Save Product click pe bhi saare tabs check karo
    document.querySelector('#productForm').addEventListener('submit', function (e) {
        let firstInvalidTab = null;
        let anyInvalid = false;

        document.querySelectorAll('.tab-pane').forEach(pane => {
            if (!validateTabPane('#' + pane.id)) {
                anyInvalid = true;
                if (!firstInvalidTab) firstInvalidTab = pane.id;
            }
        });

        if (anyInvalid) {
            e.preventDefault();
            showToast('Please fill all required fields before saving.', 'danger');

            // Us tab pe le jao jahan galti hai
            const tabButton = document.querySelector(`[data-bs-target="#${firstInvalidTab}"]`);
            if (tabButton) new bootstrap.Tab(tabButton).show();
        }
    });
});


document.getElementById('productImagesInput').addEventListener('change', async function (e) {
    const files = Array.from(e.target.files);
    const compressedFiles = [];
    const previewContainer = document.getElementById('newImagePreviewContainer');
    previewContainer.innerHTML = '';

    for (const file of files) {
        const compressed = await compressImage(file, 1200, 1200, 0.75); // max 1200x1200, 75% quality
        compressedFiles.push(compressed);

        const previewUrl = URL.createObjectURL(compressed);
        const wrap = document.createElement('div');
        wrap.className = 'product-image-thumb-wrap';
        wrap.innerHTML = `<img src="${previewUrl}">`;
        previewContainer.appendChild(wrap);
    }

    // Compressed files ko naye DataTransfer me daal ke input.files replace karo
    const dataTransfer = new DataTransfer();
    compressedFiles.forEach(f => dataTransfer.items.add(f));
    e.target.files = dataTransfer.files;
});

function compressImage(file, maxWidth, maxHeight, quality) {
    return new Promise((resolve) => {
        const img = new Image();
        const reader = new FileReader();

        reader.onload = function (event) {
            img.src = event.target.result;
        };

        img.onload = function () {
            let width = img.width;
            let height = img.height;

            // Sirf tab resize karo jab image bade dimensions ki ho
            if (width > maxWidth || height > maxHeight) {
                const ratio = Math.min(maxWidth / width, maxHeight / height);
                width = Math.round(width * ratio);
                height = Math.round(height * ratio);
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            canvas.toBlob((blob) => {
                const compressedFile = new File([blob], file.name, {
                    type: 'image/jpeg',
                    lastModified: Date.now(),
                });
                resolve(compressedFile);
            }, 'image/jpeg', quality);
        };

        reader.readAsDataURL(file);
    });
}
</script>