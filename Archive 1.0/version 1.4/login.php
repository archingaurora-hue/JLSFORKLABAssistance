<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/design.css">
</head>

<body>

    <div id="loginform" class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="w-75" id="login">
            <div class="text-center mb-4">
                <h1><b>LABAssistance</b></h1>
                <label>Laundry Management System</label>
                <h3 class="mt-2"><b>Log in</b></h3>
            </div>

            <form action="backend/login_process.php" method="POST">

                <div class="row">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" id="pwd" name="password" placeholder="Password" required>
                    </div>

                    <div class="mb-3 text-end">
                        <button type="button" class="btn btn-link text-dark p-0 text-decoration-none" onclick="Swal.fire('Please contact your administrator to reset your password.')">Forgot Password?</button>
                    </div>

                    <div class="d-grid gap-2">
                        <input type="submit" class="btn btn-dark" name="signin" value="Sign in">
                    </div>

                    <div class="text-center mt-3">
                        <a href="register.php" class="btn btn-link text-dark text-decoration-none">Don't have an account? Register</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/scripts.js"></script>
</body>

</html>