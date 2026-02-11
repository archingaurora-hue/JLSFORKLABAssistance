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
                            <input type="text" class="form-control" name="full_name" placeholder="Full Name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="name@example.com" required>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="reg_pwd" placeholder="••••••••" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('reg_pwd', 'eyeReg')">
                                        <i class="bi bi-eye" id="eyeReg"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Confirm</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="confirm_password" id="reg_conf_pwd" placeholder="••••••••" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('reg_conf_pwd', 'eyeConf')">
                                        <i class="bi bi-eye" id="eyeConf"></i>
                                    </button>
                                </div>
                            </div>
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