<?php
// Check if token/email are present
if (!isset($_GET['token']) || !isset($_GET['email'])) {
    header("Location: customer_login.php");
    exit();
}
$token = $_GET['token'];
$email = $_GET['email'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/main.css">
</head>

<body class="d-flex align-items-center justify-content-center min-h-100dvh">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">

                <div class="app-card p-4">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">Create New Password</h3>
                        <p class="text-muted small">Enter your new secure password below.</p>
                    </div>

                    <form action="backend/process_reset.php" method="POST">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="new_password" id="new_pwd" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_pwd', 'eye1')">
                                    <i class="bi bi-eye" id="eye1"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="confirm_password" id="confirm_pwd" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_pwd', 'eye2')">
                                    <i class="bi bi-eye" id="eye2"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" name="reset_password_btn" class="btn-primary-app bg-success border-success">Save New Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("bi-eye", "bi-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("bi-eye-slash", "bi-eye");
            }
        }
    </script>
</body>

</html>