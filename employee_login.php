<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Staff Login - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/main.css">
</head>

<body class="bg-dark d-flex align-items-center justify-content-center min-h-100dvh">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">

                <div class="text-center mb-4 text-white">
                    <h1 class="fw-bold">LAB<span class="text-primary">Assistance</span></h1>
                    <p class="text-white-50">Staff Login</p>
                </div>

                <div class="app-card p-4">
                    <form action="backend/employee_login_process.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Staff Email</label>
                            <input type="email" class="form-control" name="email" placeholder="staff@lab.com" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="staff_pwd" placeholder="••••••••" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('staff_pwd', 'eyeStaff')">
                                    <i class="bi bi-eye" id="eyeStaff"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" name="login" class="btn-primary-app">Access Workspace</button>
                    </form>
                </div>

                <div class="text-center mt-4">
                    <a href="customer_login.php" class="text-white-50 text-decoration-none small">Switch to Customer Login</a>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>