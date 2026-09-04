@include('userheader')

<div class="container-fluid px-4 px-lg-5 py-4">
    <h1 class="mb-4" style="font-size:1.6rem;">My Account</h1>

    <div class="row">
        @include('account.partials.sidebar')

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Manage Address</h5>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#addressModal" onclick="openAddAddressModal()">
                            <i class="fa-solid fa-plus me-1"></i> Add New Address
                        </button>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if ($addresses->count())
                        <div class="row g-3">
                            @foreach ($addresses as $address)
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 h-100 {{ $address->is_default ? 'border-danger' : '' }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <span class="badge bg-light text-dark border">{{ ucfirst($address->address_type) }}</span>
                                            @if ($address->is_default)
                                                <span class="badge bg-danger">Default</span>
                                            @endif
                                        </div>
                                        <div class="fw-bold mt-2">{{ $address->receiver_name }}</div>
                                        <div class="text-muted small">{{ $address->receiver_phone }}</div>
                                        <div class="text-muted small mt-1">
                                            {{ $address->address_line }}, {{ $address->area_locality }}, {{ $address->city }} - {{ $address->pincode }}
                                        </div>

                                        <div class="d-flex gap-2 mt-3">
                                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    onclick='openEditAddressModal(@json($address))'>Edit</button>

                                            @if (! $address->is_default)
                                                <button type="button" class="btn btn-outline-danger btn-sm set-default-btn" data-address-id="{{ $address->id }}">Set Default</button>
                                            @endif

                                            <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('Delete this address?')" class="ms-auto">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger btn-sm p-0"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-location-dot fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No saved addresses yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add / Edit Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="addressForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="addressFormMethod" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="addressModalLabel">Add New Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">Receiver Name</label>
                            <input type="text" name="receiver_name" id="addr_receiver_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Receiver Phone</label>
                            <input type="text" name="receiver_phone" id="addr_receiver_phone" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Address Line</label>
                            <input type="text" name="address_line" id="addr_address_line" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Area / Locality</label>
                            <input type="text" name="area_locality" id="addr_area_locality" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Pincode</label>
                            <input type="text" name="pincode" id="addr_pincode" class="form-control" maxlength="10" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">City</label>
                            <input type="text" name="city" id="addr_city" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Alternate Phone (optional)</label>
                            <input type="text" name="alternate_phone" id="addr_alternate_phone" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label small d-block">Address Type</label>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="address_type" id="typeHome" value="home" checked>
                                <label class="btn btn-outline-secondary btn-sm" for="typeHome">Home</label>

                                <input type="radio" class="btn-check" name="address_type" id="typeOffice" value="office">
                                <label class="btn btn-outline-secondary btn-sm" for="typeOffice">Office</label>

                                <input type="radio" class="btn-check" name="address_type" id="typeOthers" value="others">
                                <label class="btn btn-outline-secondary btn-sm" for="typeOthers">Others</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_default" id="addr_is_default" class="form-check-input" value="1">
                                <label class="form-check-label small" for="addr_is_default">Set as default address</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const addressModal = new bootstrap.Modal(document.getElementById('addressModal'));
    const addressForm = document.getElementById('addressForm');

    function openAddAddressModal() {
        document.getElementById('addressModalLabel').textContent = 'Add New Address';
        addressForm.action = "{{ route('account.addresses.store') }}";
        document.getElementById('addressFormMethod').value = 'POST';
        addressForm.reset();
    }

    function openEditAddressModal(address) {
        document.getElementById('addressModalLabel').textContent = 'Edit Address';
        addressForm.action = `/account/addresses/${address.id}`;
        document.getElementById('addressFormMethod').value = 'PUT';

        document.getElementById('addr_receiver_name').value = address.receiver_name;
        document.getElementById('addr_receiver_phone').value = address.receiver_phone;
        document.getElementById('addr_alternate_phone').value = address.alternate_phone ?? '';
        document.getElementById('addr_address_line').value = address.address_line;
        document.getElementById('addr_area_locality').value = address.area_locality;
        document.getElementById('addr_pincode').value = address.pincode;
        document.getElementById('addr_city').value = address.city;
        document.getElementById('addr_is_default').checked = address.is_default;

        document.getElementById('type' + address.address_type.charAt(0).toUpperCase() + address.address_type.slice(1)).checked = true;

        addressModal.show();
    }

    $(document).on('click', '.set-default-btn', function () {
        const addressId = $(this).data('address-id');

        $.ajax({
            url: `/account/addresses/${addressId}/set-default`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function () {
                location.reload();
            }
        });
    });
</script>
@endpush

@include('userfooter')