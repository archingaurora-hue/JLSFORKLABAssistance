<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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

                <div id="loginSection">
                    <div class="app-card p-4">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold">Log In</h3>
                        </div>

                        <form action="backend/login_process.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Email</label>
                                <input type="email" class="form-control" name="email" placeholder="Email" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label text-muted small fw-bold text-uppercase">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="login_pwd" placeholder="Password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('login_pwd', 'eyeLogin')">
                                        <i class="bi bi-eye" id="eyeLogin"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mb-4">
                                <button type="button" class="btn btn-link text-muted small p-0 text-decoration-none" onclick="toggleRecover()">Forgot Password?</button>
                            </div>

                            <button type="submit" name="signin" class="btn-primary-app">Sign In</button>
                        </form>
                    </div>
                </div>

                <div id="passRecovery" style="display: none;">
                    <div class="app-card p-4">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold">Reset Password</h3>
                            <p class="text-muted small" id="recoveryStatus">Enter your email to receive an OTP.</p>
                        </div>

                        <div id="step1">
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold text-uppercase">Email Address</label>
                                <input type="email" class="form-control" id="recovery_email" placeholder="name@example.com">
                            </div>
                            <button type="button" class="btn-primary-app" onclick="goToStep2()">Send OTP</button>
                        </div>

                        <div id="step2" style="display: none;">
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold text-uppercase text-center d-block">4-Digit OTP</label>
                                <input type="text" class="form-control text-center fs-3 fw-bold" id="otp_code" placeholder="0000" maxlength="4" style="letter-spacing: 10px;">
                            </div>
                            <button type="button" class="btn-primary-app" onclick="goToStep3()">Verify OTP</button>
                        </div>

                        <form action="backend/pass_reset.php" method="POST" id="step3" style="display: none;">
                            <input type="hidden" name="email" id="final_email">

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

                            <button type="submit" class="btn-primary-app bg-success border-success">Reset Password</button>
                        </form>

                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-link text-muted small p-0 text-decoration-none" onclick="toggleRecover()">Back to Login</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function toggleRecover() {
            var loginDiv = document.getElementById("loginSection");
            var recoverDiv = document.getElementById("passRecovery");
            if (loginDiv.style.display === "none") {
                loginDiv.style.display = "block";
                recoverDiv.style.display = "none";
            } else {
                loginDiv.style.display = "none";
                recoverDiv.style.display = "block";
            }
        }

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

        function goToStep2() {
            const email = document.getElementById('recovery_email').value;
            if (!email) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Email is required!'
                });
                return;
            }
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                icon: 'success',
                title: 'OTP sent to ' + email
            });
            document.getElementById('final_email').value = email;
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
            document.getElementById('recoveryStatus').innerText = "Enter the 4-digit code sent to your email.";
        }

        function goToStep3() {
            const otp = document.getElementById('otp_code').value;
            if (otp.length === 4) {
                document.getElementById('step2').style.display = 'none';
                document.getElementById('step3').style.display = 'block';
                document.getElementById('recoveryStatus').innerText = "Create a strong new password.";
            } else {
                Swal.fire('Invalid OTP', 'Please enter any 4 digits to proceed.', 'warning');
            }
        }

        // Handle URL Parameters for feedback
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            if (status === 'resetsuccess') {
                Swal.fire({
                    title: 'Success!',
                    text: 'Your password has been updated.',
                    icon: 'success'
                });
            } else if (status === 'mismatch') {
                Swal.fire({
                    title: 'Wait!',
                    text: 'Passwords do not match.',
                    icon: 'warning'
                });
            }
        });
    </script>
</body>

</html>