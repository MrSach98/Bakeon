@extends('admin.layouts.app')

@section('title', 'Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Category Management</h4>
        <small class="text-muted">Manage Categories, Subcategories, and Child Categories</small>
    </div>
    <div>
        <button type="button" class="btn btn-dark" onclick="openAddModal('category')">+ Add Category</button>
        <button type="button" class="btn btn-outline-dark" onclick="openAddModal('subcategory')">+ Add Subcategory</button>
        <button type="button" class="btn btn-outline-dark" onclick="openAddModal('child')">+ Add Child Category</button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="categoryTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Level</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="categoryForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="level" id="level" value="category">

                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3" id="categoryFieldWrap" style="display:none;">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">-- Select Category --</option>
                                @foreach ($topLevelCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="subcategoryFieldWrap" style="display:none;">
                            <label class="form-label">Subcategory <span class="text-danger">*</span></label>
                            <select name="subcategory_id" id="subcategory_id" class="form-select">
                                <option value="">-- Select Category First --</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" id="nameLabel">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control">
                            <img id="imagePreview" class="mt-2 rounded d-none" width="60">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
    const categoryForm = document.getElementById('categoryForm');
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    const categoryFieldWrap = document.getElementById('categoryFieldWrap');
    const subcategoryFieldWrap = document.getElementById('subcategoryFieldWrap');
    const levelInput = document.getElementById('level');
    const nameLabel = document.getElementById('nameLabel');
    const modalTitle = document.getElementById('categoryModalLabel');

    function resetSelect(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
    }

    function loadChildren(parentId, targetSelect, selectedId = null) {
        resetSelect(targetSelect, '-- Select --');
        if (!parentId) return Promise.resolve();

        return fetch(`/admin/categories/${parentId}/children`)
            .then(res => res.json())
            .then(items => {
                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    if (selectedId && item.id == selectedId) option.selected = true;
                    targetSelect.appendChild(option);
                });
            });
    }

    categorySelect.addEventListener('change', function () {
        loadChildren(this.value, subcategorySelect);
    });

    /**
     * type: 'category' | 'subcategory' | 'child'
     * Shows only the relevant parent dropdowns and updates labels/required attributes.
     */
    function configureModalForLevel(type) {
        levelInput.value = type;
        categorySelect.required = false;
        subcategorySelect.required = false;
        categoryFieldWrap.style.display = 'none';
        subcategoryFieldWrap.style.display = 'none';

        if (type === 'category') {
            modalTitle.textContent = 'Add Category';
            nameLabel.textContent = 'Category Name *';
        }

        if (type === 'subcategory') {
            modalTitle.textContent = 'Add Subcategory';
            nameLabel.textContent = 'Subcategory Name *';
            categoryFieldWrap.style.display = 'block';
            categorySelect.required = true;
        }

        if (type === 'child') {
            modalTitle.textContent = 'Add Child Category';
            nameLabel.textContent = 'Child Category Name *';
            categoryFieldWrap.style.display = 'block';
            subcategoryFieldWrap.style.display = 'block';
            categorySelect.required = true;
            subcategorySelect.required = true;
        }
    }

    function openAddModal(type) {
        categoryForm.reset();
        categoryForm.action = "{{ route('admin.categories.store') }}";
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('imagePreview').classList.add('d-none');
        resetSelect(subcategorySelect, '-- Select Category First --');
        configureModalForLevel(type);
        categoryModal.show();
    }

    function openEditModal(categoryId) {
        fetch(`/admin/categories/${categoryId}/fetch`)
            .then(res => res.json())
            .then(async data => {
                categoryForm.action = `/admin/categories/${categoryId}`;
                document.getElementById('formMethod').value = 'PUT';

                const type = data.depth === 0 ? 'category' : (data.depth === 1 ? 'subcategory' : 'child');
                configureModalForLevel(type);
                modalTitle.textContent = 'Edit ' + (type === 'category' ? 'Category' : type === 'subcategory' ? 'Subcategory' : 'Child Category');

                document.getElementById('name').value = data.name;
                document.getElementById('description').value = data.description ?? '';
                document.getElementById('sort_order').value = data.sort_order;
                document.getElementById('is_active').checked = data.is_active;

                const preview = document.getElementById('imagePreview');
                if (data.image_url) {
                    preview.src = data.image_url;
                    preview.classList.remove('d-none');
                } else {
                    preview.classList.add('d-none');
                }

                if (data.category_id) {
                    categorySelect.value = data.category_id;
                }

                if (type === 'child' && data.category_id) {
                    await loadChildren(data.category_id, subcategorySelect, data.subcategory_id);
                }

                categoryModal.show();
            });
    }

    function confirmDelete(categoryId) {
        if (confirm('Are you sure you want to delete this?')) {
            document.getElementById(`deleteForm${categoryId}`).submit();
        }
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('status-toggle')) {
            fetch(`/admin/categories/${e.target.dataset.id}/toggle-status`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            });
        }
    });

    $(function () {
        $('#categoryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.categories.data') }}",
            columns: [
                { data: 'image', name: 'image', orderable: false, searchable: false },
                { data: 'display_name', name: 'name' },
                { data: 'level', name: 'level', orderable: false, searchable: false },
                { data: 'products_count', name: 'products_count', orderable: false, searchable: false },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
        });
    });
</script>
@endpush