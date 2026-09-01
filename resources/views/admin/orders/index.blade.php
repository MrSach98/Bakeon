@extends('admin.layouts.app')

@section('title', $pageTitle)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">{{ $pageTitle }}</h4>
        <small class="text-muted">
            @if ($fixedPaymentMethod === 'online') Orders paid via Razorpay
            @elseif ($fixedPaymentMethod === 'cod') Orders to be collected at delivery
            @else All orders across payment methods @endif
        </small>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2">
            @if (! $fixedPaymentMethod)
                <div class="col-md-2">
                    <label class="form-label small">Payment Method</label>
                    <select id="filterPaymentMethod" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="online">Online</option>
                        <option value="cod">COD</option>
                    </select>
                </div>
            @endif
            <div class="col-md-2">
                <label class="form-label small">Order Status</label>
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach (\App\Models\Order::STATUS_FLOW as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Payment Status</label>
                <select id="filterPaymentStatus" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">From Date</label>
                <input type="date" id="filterDateFrom" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small">To Date</label>
                <input type="date" id="filterDateTo" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="clearFiltersBtn">Clear Filters</button>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="orderTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Placed On</th>
                    <th>Delivery Date</th>
                    <th>Amount</th>
                    @if (! $fixedPaymentMethod)<th>Payment</th>@endif
                    <th>Payment Status</th>
                    <th>Order Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        const fixedPaymentMethod = @json($fixedPaymentMethod);

        const columns = [
            { data: 'order_number', name: 'order_number' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'created_at', name: 'created_at' },
            { data: 'delivery_date', name: 'delivery_date' },
            { data: 'total_amount', name: 'total_amount' },
        ];

        if (!fixedPaymentMethod) {
            columns.push({ data: 'payment_method_badge', name: 'payment_method_badge', orderable: false, searchable: false });
        }

        columns.push(
            { data: 'payment_status_badge', name: 'payment_status_badge', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        );

        const table = $('#orderTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.orders.data') }}",
                data: function (d) {
                    d.fixed_payment_method = fixedPaymentMethod;
                    d.payment_method = $('#filterPaymentMethod').val();
                    d.status = $('#filterStatus').val();
                    d.payment_status = $('#filterPaymentStatus').val();
                    d.date_from = $('#filterDateFrom').val();
                    d.date_to = $('#filterDateTo').val();
                }
            },
            columns: columns,
            order: [[2, 'desc']],
        });

        $('#filterPaymentMethod, #filterStatus, #filterPaymentStatus, #filterDateFrom, #filterDateTo').on('change', function () {
            table.ajax.reload();
        });

        $('#clearFiltersBtn').on('click', function () {
            $('#filterPaymentMethod, #filterStatus, #filterPaymentStatus').val('');
            $('#filterDateFrom, #filterDateTo').val('');
            table.ajax.reload();
        });
    });
</script>
@endpush