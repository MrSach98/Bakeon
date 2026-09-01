@extends('admin.layouts.app')

@section('title', 'Coupons')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Coupons & Discounts</h4>
        <small class="text-muted">Manage discount codes for your store</small>
    </div>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#couponModal" onclick="openAddModal()">
        + Add Coupon
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="couponTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Min Order</th>
                    <th>Usage</th>
                    <th>Validity</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="couponModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="couponForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="couponModalLabel">Add Coupon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="code" class="form-control text-uppercase" placeholder="e.g. SWEET20" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select" onchange="toggleMaxDiscountField()">
                                <option value="flat">Flat Amount (₹)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="value" id="value" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3" id="maxDiscountWrap" style="display:none;">
                            <label class="form-label">Max Discount Cap (₹)</label>
                            <input type="number" step="0.01" name="max_discount_amount" id="max_discount_amount" class="form-control">
                            <div class="form-text">Only applies to Percentage type.</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Min Order Value (₹)</label>
                            <input type="number" step="0.01" name="min_order_value" id="min_order_value" class="form-control" value="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Usage Limit</label>
                            <input type="number" name="usage_limit" id="usage_limit" class="form-control" placeholder="Leave blank for unlimited">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Per User Limit</label>
                            <input type="number" name="per_user_limit" id="per_user_limit" class="form-control" placeholder="Leave blank for unlimited">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valid From</label>
                            <input type="date" name="valid_from" id="valid_from" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valid Until</label>
                            <input type="date" name="valid_until" id="valid_until" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Save Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const couponModal = new bootstrap.Modal(document.getElementById('couponModal'));
    const couponForm = document.getElementById('couponForm');

    function toggleMaxDiscountField() {
        const type = document.getElementById('type').value;
        document.getElementById('maxDiscountWrap').style.display = type === 'percentage' ? 'block' : 'none';
    }

    function openAddModal() {
        document.getElementById('couponModalLabel').textContent = 'Add Coupon';
        couponForm.action = "{{ route('admin.coupons.store') }}";
        document.getElementById('formMethod').value = 'POST';
        couponForm.reset();
        toggleMaxDiscountField();
    }

    function openEditModal(id) {
        fetch(`/admin/coupons/${id}/fetch`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('couponModalLabel').textContent = 'Edit Coupon';
                couponForm.action = `/admin/coupons/${id}`;
                document.getElementById('formMethod').value = 'PUT';

                document.getElementById('code').value = data.code;
                document.getElementById('type').value = data.type;
                document.getElementById('value').value = data.value;
                document.getElementById('max_discount_amount').value = data.max_discount_amount ?? '';
                document.getElementById('min_order_value').value = data.min_order_value;
                document.getElementById('usage_limit').value = data.usage_limit ?? '';
                document.getElementById('per_user_limit').value = data.per_user_limit ?? '';
                document.getElementById('valid_from').value = data.valid_from ?? '';
                document.getElementById('valid_until').value = data.valid_until ?? '';
                document.getElementById('description').value = data.description ?? '';
                document.getElementById('is_active').checked = data.is_active;

                toggleMaxDiscountField();
                couponModal.show();
            });
    }

    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this coupon?')) {
            document.getElementById(`deleteForm${id}`).submit();
        }
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('status-toggle')) {
            fetch(`/admin/coupons/${e.target.dataset.id}/toggle-status`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            });
        }
    });

    $(function () {
        $('#couponTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.coupons.data') }}",
            columns: [
                { data: 'code', name: 'code' },
                { data: 'type', name: 'type' },
                { data: 'value', name: 'value' },
                { data: 'min_order_value', name: 'min_order_value' },
                { data: 'usage', name: 'usage', orderable: false, searchable: false },
                { data: 'validity', name: 'validity', orderable: false, searchable: false },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
        });
    });
</script>
@endpush