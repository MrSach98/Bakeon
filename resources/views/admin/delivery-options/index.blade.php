@extends('admin.layouts.app')

@section('title', 'Delivery Options')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Delivery Options</h4>
        <small class="text-muted">Same Day, Midnight, Fixed Time, Express Delivery</small>
    </div>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#deliveryModal" onclick="openAddModal()">
        + Add Delivery Option
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="deliveryTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Order Cutoff</th>
                    <th>Delivery Window</th>
                    <th>Extra Charge</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="deliveryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deliveryForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="deliveryModalLabel">Add Delivery Option</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Midnight Delivery" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Order Cutoff Time</label>
                        <input type="time" name="order_cutoff_time" id="order_cutoff_time" class="form-control">
                        <div class="form-text">Last time a customer can place this order type.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Delivery Window Start</label>
                            <input type="time" name="delivery_window_start" id="delivery_window_start" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Delivery Window End</label>
                            <input type="time" name="delivery_window_end" id="delivery_window_end" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Extra Charge (₹)</label>
                        <input type="number" step="0.01" name="extra_charge" id="extra_charge" class="form-control" value="0">
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Save Delivery Option</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const deliveryModal = new bootstrap.Modal(document.getElementById('deliveryModal'));
    const deliveryForm = document.getElementById('deliveryForm');

    function openAddModal() {
        document.getElementById('deliveryModalLabel').textContent = 'Add Delivery Option';
        deliveryForm.action = "{{ route('admin.delivery-options.store') }}";
        document.getElementById('formMethod').value = 'POST';
        deliveryForm.reset();
    }

    function openEditModal(id) {
        fetch(`/admin/delivery-options/${id}/fetch`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('deliveryModalLabel').textContent = 'Edit Delivery Option';
                deliveryForm.action = `/admin/delivery-options/${id}`;
                document.getElementById('formMethod').value = 'PUT';
                document.getElementById('name').value = data.name;
                document.getElementById('order_cutoff_time').value = data.order_cutoff_time ?? '';
                document.getElementById('delivery_window_start').value = data.delivery_window_start ?? '';
                document.getElementById('delivery_window_end').value = data.delivery_window_end ?? '';
                document.getElementById('extra_charge').value = data.extra_charge;
                document.getElementById('is_active').checked = data.is_active;
                deliveryModal.show();
            });
    }

    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this delivery option?')) {
            document.getElementById(`deleteForm${id}`).submit();
        }
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('status-toggle')) {
            fetch(`/admin/delivery-options/${e.target.dataset.id}/toggle-status`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            });
        }
    });

    $(function () {
        $('#deliveryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.delivery-options.data') }}",
            columns: [
                { data: 'name', name: 'name' },
                { data: 'order_cutoff_time', name: 'order_cutoff_time' },
                { data: 'window', name: 'window', orderable: false, searchable: false },
                { data: 'extra_charge', name: 'extra_charge' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
        });
    });
</script>
@endpush