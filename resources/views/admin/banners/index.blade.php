@extends('admin.layouts.app')

@section('title', 'Banners')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Homepage Banners</h4>
        <small class="text-muted">Manage the hero slider shown on the storefront homepage</small>
    </div>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#bannerModal" onclick="openAddModal()">
        + Add Banner
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="bannerTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Image</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="bannerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="bannerForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="bannerModalLabel">Add Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Banner Type <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select" required>
                            @foreach (\App\Models\Banner::TYPES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Controls where this banner appears on the homepage.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subtitle</label>
                        <input type="text" name="subtitle" id="subtitle" class="form-control">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Button Text</label>
                            <input type="text" name="button_text" id="button_text" class="form-control" placeholder="e.g. Shop Now">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Link URL</label>
                            <input type="text" name="link_url" id="link_url" class="form-control" placeholder="/category/birthday-cakes">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Banner Image <span class="text-danger" id="imageRequiredMark">*</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <img id="imagePreview" class="mt-2 rounded d-none" style="max-height:120px;">
                        <div class="form-text">Recommended size: 1600x600px</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Save Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const bannerModal = new bootstrap.Modal(document.getElementById('bannerModal'));
    const bannerForm = document.getElementById('bannerForm');

    function openAddModal() {
        document.getElementById('bannerModalLabel').textContent = 'Add Banner';
        bannerForm.action = "{{ route('admin.banners.store') }}";
        document.getElementById('formMethod').value = 'POST';
        bannerForm.reset();
        document.getElementById('imagePreview').classList.add('d-none');
        document.getElementById('imageRequiredMark').style.display = 'inline';
        bannerForm.querySelector('input[name="image"]').required = true;
    }

    function openEditModal(id) {
        fetch(`/admin/banners/${id}/fetch`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('bannerModalLabel').textContent = 'Edit Banner';
                bannerForm.action = `/admin/banners/${id}`;
                document.getElementById('formMethod').value = 'PUT';
                document.getElementById('type').value = data.type ?? 'hero';
                document.getElementById('title').value = data.title ?? '';
                document.getElementById('subtitle').value = data.subtitle ?? '';
                document.getElementById('button_text').value = data.button_text ?? '';
                document.getElementById('link_url').value = data.link_url ?? '';
                document.getElementById('sort_order').value = data.sort_order;
                document.getElementById('is_active').checked = data.is_active;

                const preview = document.getElementById('imagePreview');
                preview.src = data.image_url;
                preview.classList.remove('d-none');

                document.getElementById('imageRequiredMark').style.display = 'none';
                bannerForm.querySelector('input[name="image"]').required = false;

                bannerModal.show();
            });
    }

    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this banner?')) {
            document.getElementById(`deleteForm${id}`).submit();
        }
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('status-toggle')) {
            fetch(`/admin/banners/${e.target.dataset.id}/toggle-status`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            });
        }
    });

    $(function () {
        $('#bannerTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.banners.data') }}",
            columns: [
                { data: 'image', name: 'image', orderable: false, searchable: false },
                { data: 'type_label', name: 'type' },
                { data: 'title', name: 'title' },
                { data: 'sort_order', name: 'sort_order' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
        });
    });
</script>
@endpush