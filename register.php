<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Register - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/main.css">
</head>

<body class="d-flex align-items-center justify-content-center min-h-100dvh py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">

                <div class="text-center mb-4">
                    <h1 class="fw-bold display-6">Create Account</h1>
                    <p class="text-muted">Join LABAssistance today</p>
                </div>

                <div class="app-card p-4">
                    <form action="backend/register_process.php" method="POST">

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Full Name</label>
                            <input type="text" class="form-control" name="full_name" placeholder="Juan Dela Cruz" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="name@example.com" required>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Password</label>
                                <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Confirm</label>
                                <input type="password" class="form-control" name="confirm_password" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="smooth-checkbox">
                                <input type="checkbox" name="terms" required>
                                <span class="checkmark"></span>
                                <span class="text-muted small">I agree to the Terms & Conditions</span>
                            </label>
                        </div>

                        <button type="submit" name="register" class="btn-primary-app">Register Account</button>
                    </form>
                </div>

                <div class="text-center mt-4">
                    <span class="text-muted">Already have an account?</span>
                    <a href="customer_login.php" class="text-dark fw-bold text-decoration-none">Log In</a>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>