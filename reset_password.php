<?php
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
                            <ul id="reset_criteria" class="list-unstyled small mt-2 mb-0" style="font-size: 0.8rem;">
                                <li id="res_crit_len" class="text-danger"><i class="bi bi-x-circle me-1"></i>8+ characters</li>
                                <li id="res_crit_up" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 uppercase letter</li>
                                <li id="res_crit_low" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 lowercase letter</li>
                                <li id="res_crit_num" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 number</li>
                                <li id="res_crit_spec" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 special character</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="confirm_password" id="confirm_pwd" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_pwd', 'eye2')">
                                    <i class="bi bi-eye" id="eye2"></i>
                                </button>
                            </div>
                            <small id="passwordMatchText" class="text-danger d-none fw-bold mt-1"><i class="bi bi-exclamation-circle me-1"></i>Passwords do not match</small>
                        </div>

                        <button type="submit" name="reset_password_btn" id="submitBtn" class="btn-primary-app bg-success border-success w-100" disabled>Save New Password</button>
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

        // Password Validation Rules
        const pwd = document.getElementById('new_pwd');
        const confPwd = document.getElementById('confirm_pwd');
        const matchText = document.getElementById('passwordMatchText');
        const submitBtn = document.getElementById('submitBtn');

        function updateCriterion(id, isMet) {
            const el = document.getElementById(id);
            const icon = el.querySelector('i');
            if (isMet) {
                el.classList.replace('text-danger', 'text-success');
                icon.classList.replace('bi-x-circle', 'bi-check-circle');
            } else {
                el.classList.replace('text-success', 'text-danger');
                icon.classList.replace('bi-check-circle', 'bi-x-circle');
            }
        }

        function validatePassword() {
            const p = pwd.value;
            const c = confPwd.value;

            const hasLen = p.length >= 8;
            const hasUp = /[A-Z]/.test(p);
            const hasLow = /[a-z]/.test(p);
            const hasNum = /[0-9]/.test(p);
            const hasSpec = /[^A-Za-z0-9]/.test(p);

            updateCriterion('res_crit_len', hasLen);
            updateCriterion('res_crit_up', hasUp);
            updateCriterion('res_crit_low', hasLow);
            updateCriterion('res_crit_num', hasNum);
            updateCriterion('res_crit_spec', hasSpec);

            const isStrong = hasLen && hasUp && hasLow && hasNum && hasSpec;

            // Check match
            const isMatch = (c.length > 0 && p === c);
            if (c.length > 0 && !isMatch) {
                matchText.classList.remove('d-none');
            } else {
                matchText.classList.add('d-none');
            }

            // Enable submit button only if strong and matched
            submitBtn.disabled = !(isStrong && isMatch);
        }

        pwd.addEventListener('input', validatePassword);
        confPwd.addEventListener('input', validatePassword);

        // Initialize state on load
        validatePassword();
    </script>
</body>

</html>