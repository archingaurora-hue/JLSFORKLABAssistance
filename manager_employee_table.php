<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header("Location: employee_login.php");
    exit();
}

// INCLUDE CONNECTION
require 'backend/db_conn.php';

// Fetch only Employees
$result = $conn->query("SELECT * FROM `User` WHERE role = 'Employee'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Table - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/design.css">
</head>

<body class="bg-light">

    <div class="container py-5">
        <div class="text-center mb-4">
            <h1 class="fw-bold">LABAssistance</h1>
            <p class="text-muted">Laundry Management System</p>
            <h2 class="mt-2 fw-bold text-uppercase">Employee Table</h2>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th scope="col" class="text-muted small text-uppercase">ID</th>
                                <th scope="col" class="text-muted small text-uppercase">Employee Name</th>
                                <th scope="col" class="text-muted small text-uppercase">Email</th>
                                <th scope="col" class="text-end text-muted small text-uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['user_id']; ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-link text-dark p-0 me-2" onclick="editEmployee(<?php echo $row['user_id']; ?>, '<?php echo $row['full_name']; ?>', '<?php echo $row['email']; ?>')">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <a href="backend/employee_crud.php?delete=<?php echo $row['user_id']; ?>" class="btn btn-link text-dark p-0" onclick="return confirm('Are you sure you want to delete this employee?');">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No employees found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-3">
                    <button class="btn btn-success fw-bold px-4" data-bs-toggle="modal" data-bs-target="#employeeModal" onclick="resetForm()">
                        + Add Employee
                    </button>
                </div>

                <div class="mt-3">
                    <a href="manager_dashboard.php" class="text-decoration-none text-muted">&larr; Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Add New Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="backend/employee_crud.php" method="POST">
                        <input type="hidden" name="user_id" id="emp_id">

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" id="emp_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="emp_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="emp_password" placeholder="Default or New Password" required>
                            <small class="text-muted" id="passHelp">Required for new employees.</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="save_employee" class="btn btn-dark">Save Employee</button>
                        </div>
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

            // Password not mandatory for edit (logic handled in backend)
            document.getElementById('emp_password').required = false;
            document.getElementById('emp_password').placeholder = "Leave blank to keep current";
            document.getElementById('passHelp').innerText = "Leave blank to keep current password.";

            var myModal = new bootstrap.Modal(document.getElementById('employeeModal'));
            myModal.show();
        }

        function resetForm() {
            document.getElementById('modalTitle').innerText = "Add New Employee";
            document.getElementById('emp_id').value = "";
            document.getElementById('emp_name').value = "";
            document.getElementById('emp_email').value = "";
            document.getElementById('emp_password').required = true;
            document.getElementById('emp_password').placeholder = "Password";
            document.getElementById('passHelp').innerText = "Required for new employees.";
        }
    </script>
</body>

</html>