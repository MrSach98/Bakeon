@include('userheader')

<div class="container-fluid px-4 px-lg-5 py-4">
    <h1 class="mb-4" style="font-size:1.6rem;">My Account</h1>

    <div class="row">
        @include('account.partials.sidebar')

        <div class="col-lg-9">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Profile Details</h5>
                    <form method="POST" action="{{ route('account.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label small">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $accountUser->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $accountUser->email) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Phone</label>
                            <input type="text" name="phone" class="form-control" maxlength="10" value="{{ old('phone', $accountUser->phone) }}">
                        </div>

                        <button type="submit" class="btn btn-danger">Save Changes</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Change Password</h5>
                    <form method="POST" action="{{ route('account.profile.update-password') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label small">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required minlength="6">
                        </div>

                        <button type="submit" class="btn btn-outline-danger">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('userfooter')