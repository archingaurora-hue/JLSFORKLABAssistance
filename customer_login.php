<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/main.css">
</head>

<body class="d-flex align-items-center justify-content-center min-h-100dvh">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">

                <div class="text-center mb-4">
                    <h1 class="fw-bold display-6">LABAssistance</h1>
                    <p class="text-muted">Laundry Management System</p>
                </div>

                <div class="app-card p-4">
                    <form action="backend/login_process.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="name@example.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                        </div>

                        <div class="d-flex justify-content-end mb-4">
                            <a href="#" class="text-decoration-none small text-muted" onclick="toggleRecover()">Forgot Password?</a>
                        </div>

                        <button type="submit" name="signin" class="btn-primary-app">Sign In</button>
                    </form>
                </div>

                <div class="text-center mt-4">
                    <span class="text-muted">New here?</span>
                    <a href="register.php" class="text-dark fw-bold text-decoration-none">Create Account</a>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>