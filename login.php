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

        <div class="col-11 col-md-6 col-lg-5 p-4" id="login">
            <div class="text-center mb-5">
                <h1><b>LABAssistance</b></h1>
                <label class="text-muted">Laundry Management System</label>
                <h3 class="mt-3"><b>Log in</b></h3>
            </div>

            <form action="backend/login_process.php" method="POST">
                <div class="row">
                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control p-3" name="email" placeholder="Email" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control p-3" name="password" placeholder="Password" required>
                    </div>
                    <div class="mb-4 text-end">
                        <button type="button" class="btn btn-link text-dark p-0 text-decoration-none" onclick="toggleRecover()">Forgot Password?</button>
                    </div>
                    <div class="d-grid gap-2 mb-4">
                        <input type="submit" class="btn btn-dark p-3" name="signin" value="Sign in">
                    </div>
                    <div class="text-center">
                        <a href="register.php" class="btn btn-link text-dark text-decoration-none">Don't have an account? Register</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-11 col-md-6 col-lg-5 p-4" id="passRecovery" style="display: none;">
            <div class="text-center mb-4">
                <h1><b>LABAssistance</b></h1>
                <h3 class="mt-3"><b>Reset Password</b></h3>
                <p class="text-muted" id="recoveryStatus">Enter your email to receive an OTP.</p>
            </div>

            <div id="step1">
                <div class="mb-4">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control p-3" id="recovery_email" placeholder="name@example.com">
                </div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-dark p-3" onclick="goToStep2()">Send OTP</button>
                </div>
            </div>

            <div id="step2" style="display: none;">
                <div class="mb-4">
                    <label class="form-label">Enter 4-Digit OTP</label>
                    <input type="text" class="form-control p-3 text-center" id="otp_code" placeholder="0000" maxlength="4" style="letter-spacing: 15px; font-size: 1.5rem;">
                </div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-dark p-3" onclick="goToStep3()">Enter</button>
                </div>
            </div>

            <form action="backend/pass_reset.php" method="POST" id="step3" style="display: none;">
                <input type="hidden" name="email" id="final_email">

                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control p-3" name="new_password" id="new_pwd" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_pwd', 'eye1')">
                            <i class="bi bi-eye" id="eye1"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirm New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control p-3" name="confirm_password" id="confirm_pwd" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_pwd', 'eye2')">
                            <i class="bi bi-eye" id="eye2"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success p-3">Reset Password</button>
                </div>
            </form>

            <div class="text-center mt-4">
                <button type="button" class="btn btn-link text-dark p-0 text-decoration-none" onclick="toggleRecover()">Back to Login</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function toggleRecover() {
            var loginDiv = document.getElementById("login");
            var recoverDiv = document.getElementById("passRecovery");
            if (loginDiv.style.display === "none") {
                loginDiv.style.display = "block";
                recoverDiv.style.display = "none";
            } else {
                loginDiv.style.display = "none";
                recoverDiv.style.display = "block";
            }
        }

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error')) {
            Swal.fire('Error', 'This email does not exist!', 'error');
        } else if (urlParams.has('success')) {
            Swal.fire('Success', 'Recovery instructions (simulated) have been sent to your email!', 'success');
        }

        // Toggle password visibility
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

        // Transition from Email to OTP
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


            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            Toast.fire({
                icon: 'success',
                title: 'OTP sent to ' + email
            });

            document.getElementById('final_email').value = email;
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
            document.getElementById('recoveryStatus').innerText = "Enter the 4-digit code sent to your email.";
        }

        // Transition from OTP to New Password
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

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');

            if (status === 'resetsuccess') {
                Swal.fire({
                    title: 'Success!',
                    text: 'Your password has been updated. You can now log in.',
                    icon: 'success',
                    confirmButtonColor: '#212529',
                    background: '#ffffff',
                    showConfirmButton: true
                });
            } else if (status === 'mismatch') {
                Swal.fire({
                    title: 'Wait!',
                    text: 'Passwords do not match. Please try again.',
                    icon: 'warning',
                    confirmButtonColor: '#212529'
                });
            } else if (status === 'error') {
                Swal.fire({
                    title: 'System Error',
                    text: 'Something went wrong. Please contact support.',
                    icon: 'error',
                    confirmButtonColor: '#212529'
                });
            }
        });
    </script>


</body>

</html>