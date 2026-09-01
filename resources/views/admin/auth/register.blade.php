<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakery Admin Portal | Register</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-chocolate: #4A2E2B;
            --secondary-cream: #FFF8F0;
            --accent-pink: #E88D8D;
            --accent-gold: #D4A373;
            --text-dark: #2B2B2B;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #FFF8F0 0%, #F4E8DD 100%);
            color: var(--text-dark);
            min-height: 100vh;
        }

        .brand-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary-chocolate);
            font-weight: 700;
        }

        .cake-card {
            background: #FFFFFF;
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(74, 46, 43, 0.08);
            overflow: hidden;
        }

        .cake-icon-wrapper {
            width: 70px;
            height: 70px;
            background-color: var(--secondary-cream);
            color: var(--accent-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px auto;
            font-size: 30px;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.03);
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #E2D7CD;
            background-color: #FAFAFA;
        }

        .form-control:focus {
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 0.25rem rgba(212, 163, 115, 0.25);
            background-color: #FFFFFF;
        }

        .btn-bakery {
            background-color: var(--primary-chocolate);
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-bakery:hover {
            background-color: #36211F;
            color: #FFFFFF;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 46, 43, 0.2);
        }

        .bakery-link {
            color: var(--accent-pink);
            text-decoration: none;
            font-weight: 500;
        }

        .bakery-link:hover {
            color: var(--primary-chocolate);
            text-decoration: underline;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card cake-card p-3">
                <div class="card-body p-4">
                    
                    <!-- Cake Shop Branding Header -->
                    <div class="text-center mb-4">
                        <div class="cake-icon-wrapper">
                            <i class="fa-solid fa-user-plus"></i>
                        </div>
                        <h3 class="brand-title mb-1">Join Sweet Bakes</h3>
                        <p class="text-muted small">Create New Administrator Account</p>
                    </div>

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="alert alert-danger border-0 rounded-3 mb-4">
                            <ul class="mb-0 ps-3 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Registration Form -->
                    <form method="POST" action="{{ route('admin.register.submit') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Full Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="John Doe" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-medium">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="admin@sweetbakes.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-medium">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-medium">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
                        </div>

                        <button type="submit" class="btn btn-bakery w-100 mb-3">
                            <i class="fa-solid fa-user-check me-2"></i> Register Account
                        </button>

                        <p class="text-center small text-muted mb-0">
                            Already have an account? <a href="{{ route('admin.login') }}" class="bakery-link">Sign in here</a>
                        </p>
                    </form>

                </div>
            </div>
            
            <!-- Footer Copyright -->
            <p class="text-center text-muted small mt-4">
                &copy; {{ date('Y') }} Sweet Bakes Administration. All rights reserved.
            </p>
        </div>
    </div>
</div>

</body>
</html>