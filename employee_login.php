<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Staff Login - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/design.css">
</head>

<body class="bg-dark">

    <div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="card p-4 shadow-lg rounded-3" style="width: 100%; max-width: 400px;">

            <div class="text-center mb-4">
                <h2 class="fw-bold">LABAssistance</h2>
                <p class="text-muted">Laundry Management System</p>
                <h3 class="mt-2">Staff Log In</h3>
            </div>

            <form action="backend/employee_login_process.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <input type="submit" class="btn btn-dark" name="login" value="Sign In">
                </div>

                <div class="text-center">
                    <a href="#" class="text-muted text-decoration-none small">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>