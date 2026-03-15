<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header("Location: employee_login.php");
    exit();
}
require 'backend/db_conn.php';
$result = $conn->query("SELECT * FROM `User` WHERE role = 'Employee'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Employees</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/main.css">
</head>

<body class="bg-light">

    <nav class="navbar navbar-light bg-white shadow-sm sticky-top mb-4">
        <div class="container">
            <a href="manager_dashboard.php" class="btn btn-sm btn-light rounded-circle"><i class="bi bi-arrow-left"></i></a>
            <span class="navbar-brand mb-0 h1 fw-bold fs-5 mx-auto">Employees</span>
            <button class="btn btn-sm btn-dark rounded-circle" data-bs-toggle="modal" data-bs-target="#employeeModal" onclick="resetForm()">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
    </nav>

    <div class="container page-container">

        <div class="app-card p-4 d-none d-md-block">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result->data_seek(0);
                    while ($row = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo $row['user_id']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-light rounded-circle me-1" onclick="editEmployee(<?php echo $row['user_id']; ?>, '<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>')"><i class="bi bi-pencil"></i></button>
                                <a href="backend/employee_crud.php?delete=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-light text-danger rounded-circle" onclick="return confirm('Delete?');"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="d-md-none">
            <?php
            $result->data_seek(0);
            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
            ?>
                    <div class="app-card mb-3 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></span>
                            <span class="badge bg-light text-dark border">ID: <?php echo $row['user_id']; ?></span>
                        </div>
                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($row['email']); ?></p>

                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-dark flex-grow-1 rounded-pill" onclick="editEmployee(<?php echo $row['user_id']; ?>, '<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>')">
                                Edit
                            </button>
                            <a href="backend/employee_crud.php?delete=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-outline-danger flex-grow-1 rounded-pill" onclick="return confirm('Delete this employee?');">
                                Delete
                            </a>
                        </div>
                    </div>
                <?php
                endwhile;
            else:
                ?>
                <p class="text-center text-muted">No employees found.</p>
            <?php endif; ?>
        </div>

    </div>

    <div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="backend/employee_crud.php" method="POST" id="employeeForm" novalidate>
                        <input type="hidden" name="user_id" id="emp_id">

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Full Name</label>
                            <input type="text" class="form-control" name="full_name" id="emp_name" placeholder="Full Name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Email</label>
                            <input type="email" class="form-control" name="email" id="emp_email" placeholder="Email" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Password</label>
                            <input type="password" class="form-control" name="password" id="emp_password" autocomplete="new-password" placeholder="Password">
                            <small class="text-muted" id="passHelp">Required for new employees.</small>

                            <ul id="emp_criteria" class="list-unstyled small mt-2 mb-0" style="font-size: 0.8rem;">
                                <li id="emp_crit_len" class="text-danger"><i class="bi bi-x-circle me-1"></i>8+ characters</li>
                                <li id="emp_crit_up" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 uppercase letter</li>
                                <li id="emp_crit_low" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 lowercase letter</li>
                                <li id="emp_crit_num" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 number</li>
                                <li id="emp_crit_spec" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 special character</li>
                            </ul>
                        </div>

                        <div class="mb-3" id="confirm_password_div">
                            <label class="form-label small text-muted fw-bold">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" id="emp_confirm_password" placeholder="Confirm Password">
                            <small id="emp_passwordMatchText" class="text-danger d-none fw-bold mt-1"><i class="bi bi-exclamation-circle me-1"></i>Passwords do not match</small>
                        </div>

                        <div id="formError" class="alert alert-danger py-2 small d-none" role="alert">
                            <i class="bi bi-exclamation-circle me-1"></i> Please fill in all required fields.
                        </div>

                        <button type="submit" name="save_employee" class="btn-primary-app w-100">Save Employee</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editEmployee(id, name, email) {
            document.getElementById('modalTitle').innerText = "Edit Employee";
            document.getElementById('emp_id').value = id;
            document.getElementById('emp_name').value = name;
            document.getElementById('emp_email').value = email;

            // Password logic for editing
            document.getElementById('emp_password').required = false;
            document.getElementById('emp_password').placeholder = "Leave blank to keep current";
            document.getElementById('passHelp').innerText = "Leave blank to keep current password.";

            // Hide error message if it was previously triggered
            document.getElementById('formError').classList.add('d-none');

            var myModal = new bootstrap.Modal(document.getElementById('employeeModal'));
            myModal.show();
        }

        function resetForm() {
            document.getElementById('modalTitle').innerText = "Add Employee";
            document.getElementById('emp_id').value = "";
            document.getElementById('emp_name').value = "";
            document.getElementById('emp_email').value = "";
            document.getElementById('emp_password').value = "";

            // Password logic for new entry
            document.getElementById('emp_password').required = true;
            document.getElementById('emp_password').placeholder = "Password";
            document.getElementById('passHelp').innerText = "Required for new employees.";

            // Hide error message if it was previously triggered
            document.getElementById('formError').classList.add('d-none');
        }

        // Intercept form submission to show red text instead of alert
        document.getElementById('employeeForm').addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault(); // Stop form from submitting
                event.stopPropagation();

                // Show the red error box
                document.getElementById('formError').classList.remove('d-none');

                // Optional: Adds Bootstrap's native red borders to empty fields
                this.classList.add('was-validated');
            }
        });

        // Hide the error message as soon as the user starts typing again
        document.getElementById('employeeForm').addEventListener('input', function() {
            document.getElementById('formError').classList.add('d-none');
        });
        const empPwd = document.getElementById('emp_password');
        const empConfPwd = document.getElementById('emp_confirm_password');
        const empMatchText = document.getElementById('emp_passwordMatchText');
        const empCriteria = document.getElementById('emp_criteria');
        const isEditing = document.getElementById('emp_id');
        const passHelp = document.getElementById('passHelp');

        function updateEmpCriterion(id, isMet) {
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

        function validateEmpPassword() {
            const p = empPwd.value;
            const c = empConfPwd.value;

            const hasLen = p.length >= 8;
            const hasUp = /[A-Z]/.test(p);
            const hasLow = /[a-z]/.test(p);
            const hasNum = /[0-9]/.test(p);
            const hasSpec = /[^A-Za-z0-9]/.test(p);

            updateEmpCriterion('emp_crit_len', hasLen);
            updateEmpCriterion('emp_crit_up', hasUp);
            updateEmpCriterion('emp_crit_low', hasLow);
            updateEmpCriterion('emp_crit_num', hasNum);
            updateEmpCriterion('emp_crit_spec', hasSpec);

            const isStrong = hasLen && hasUp && hasLow && hasNum && hasSpec;

            // Check match
            if (c.length > 0 && p !== c) {
                empMatchText.classList.remove('d-none');
            } else {
                empMatchText.classList.add('d-none');
            }

            // If editing and blank, hide the help text and consider valid
            if (isEditing.value !== "" && p === "") {
                passHelp.classList.remove('d-none');
                return true;
            }

            passHelp.classList.add('d-none');

            if (p === "" && isEditing.value === "") return false; // Block new employee empty password

            return isStrong && (p === c);
        }

        empPwd.addEventListener('input', validateEmpPassword);
        empConfPwd.addEventListener('input', validateEmpPassword);

        document.getElementById('employeeForm').addEventListener('submit', function(event) {
            if (!validateEmpPassword()) {
                event.preventDefault();
                document.getElementById('formError').classList.remove('d-none');
                document.getElementById('formError').innerHTML = '<i class="bi bi-exclamation-circle me-1"></i> Please ensure passwords match and meet requirements.';
            }
        });
    </script>
</body>

</html>