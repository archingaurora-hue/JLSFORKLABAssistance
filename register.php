<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Register - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./css/main.css">
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100 py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">

                <div class="text-center mb-4">
                    <h1 class="fw-bold display-6">Create Account</h1>
                    <p class="text-muted">Join LABAssistance today</p>
                </div>

                <div class="app-card p-4 shadow-sm rounded bg-white">
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
                                <label class="form-label text-muted small fw-bold text-uppercase">Confirm Password</label>
                                <div class="input-group mb-1">
                                    <input type="password" class="form-control" name="confirm_password" id="reg_conf_pwd" placeholder="••••••••" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('reg_conf_pwd', 'eyeConf')">
                                        <i class="bi bi-eye" id="eyeConf"></i>
                                    </button>
                                </div>
                                <small id="passwordMatchText" class="text-danger d-none">Passwords do not match</small>
                            </div>
                        </div>

                        <div class="form-check mb-4 mt-2">
                            <input class="form-check-input" type="checkbox" value="" id="agreeTerms" required>
                            <label class="form-check-label text-muted small" for="agreeTerms">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal" class="text-primary fw-bold text-decoration-underline">Terms and Conditions</a>
                                <br class="d-md-none">
                                <span class="text-secondary fst-italic" style="font-size: 0.85em;">(Click to read)</span>
                            </label>
                        </div>

                        <button type="submit" name="register" id="submitBtn" class="btn btn-primary w-100 py-2 fw-bold">Register Account</button>
                    </form>
                </div>

                <div class="text-center mt-4">
                    <span class="text-muted">Already have an account?</span>
                    <a href="customer_login.php" class="text-dark fw-bold text-decoration-none">Log In</a>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="termsModalLabel">Terms & Conditions and Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-muted small">
                    <h6>Data Privacy Act of 2012 (Republic Act No. 10173)</h6>
                    <p>By registering for an account with LABAssistance, you acknowledge and agree to the collection, processing, and storage of your personal data in accordance with the Philippine Data Privacy Act of 2012.</p>

                    <p><strong>1. Collection of Personal Information</strong><br>
                        We collect personal information such as your full name and email address strictly for the purpose of creating your account, facilitating our services, and verifying your identity.</p>

                    <p><strong>2. Use and Protection of Data</strong><br>
                        Your data will be stored securely in our database. We implement reasonable organizational, physical, and technical security measures to protect your personal information from unauthorized access, alteration, or disclosure.</p>

                    <p><strong>3. Non-Disclosure</strong><br>
                        LABAssistance will not sell, rent, or share your personal information with third parties without your explicit consent, except when required by law.</p>

                    <p><strong>4. User Rights</strong><br>
                        Under the Data Privacy Act, you have the right to access, correct, or request the deletion of your personal data from our systems at any time.</p>

                    <p>By checking the "I agree" box, you confirm that you have read, understood, and consented to these terms.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        // Real-time password matching validation
        const pwd = document.getElementById('reg_pwd');
        const confPwd = document.getElementById('reg_conf_pwd');
        const matchText = document.getElementById('passwordMatchText');
        const submitBtn = document.getElementById('submitBtn');

        function checkPasswordsMatch() {
            if (confPwd.value === '') {
                matchText.classList.add('d-none');
                submitBtn.disabled = false;
                return;
            }

            if (pwd.value !== confPwd.value) {
                matchText.classList.remove('d-none');
                submitBtn.disabled = true;
            } else {
                matchText.classList.add('d-none');
                submitBtn.disabled = false;
            }
        }

        pwd.addEventListener('input', checkPasswordsMatch);
        confPwd.addEventListener('input', checkPasswordsMatch);
    </script>
</body>

</html>