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
                    // Reset pointer for desktop loop
                    $result->data_seek(0);
                    while ($row = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo $row['user_id']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-light rounded-circle me-1" onclick="editEmployee(<?php echo $row['user_id']; ?>, '<?php echo $row['full_name']; ?>', '<?php echo $row['email']; ?>')"><i class="bi bi-pencil"></i></button>
                                <a href="backend/employee_crud.php?delete=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-light text-danger rounded-circle" onclick="return confirm('Delete?');"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="d-md-none">
            <?php
            // Reset pointer for mobile loop
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
                            <button class="btn btn-sm btn-outline-dark flex-grow-1 rounded-pill" onclick="editEmployee(<?php echo $row['user_id']; ?>, '<?php echo $row['full_name']; ?>', '<?php echo $row['email']; ?>')">
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
                    <form action="backend/employee_crud.php" method="POST">
                        <input type="hidden" name="user_id" id="emp_id">
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Full Name</label>
                            <input type="text" class="form-control" name="full_name" id="emp_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Email</label>
                            <input type="email" class="form-control" name="email" id="emp_email" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small text-muted fw-bold">Password</label>
                            <input type="password" class="form-control" name="password" id="emp_password" placeholder="Password">
                            <small class="text-muted" id="passHelp">Required for new employees.</small>
                        </div>
                        <button type="submit" name="save_employee" class="btn-primary-app">Save Employee</button>
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
            document.getElementById('emp_password').required = false;
            document.getElementById('emp_password').placeholder = "Leave blank to keep current";
            document.getElementById('passHelp').innerText = "Leave blank to keep current password.";
            var myModal = new bootstrap.Modal(document.getElementById('employeeModal'));
            myModal.show();
        }

        function resetForm() {
            document.getElementById('modalTitle').innerText = "Add Employee";
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