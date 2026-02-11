<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/design.css">
    <link rel="stylesheet" href="./css/dashboard.css">
</head>

<body class="bg-light">

    <div class="container d-flex flex-column" style="min-height: 100dvh;">

        <div class="row pt-3 flex-shrink-0">
            <div class="col-12 d-flex justify-content-end">
                <a href="customer_login.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">Log Out</a>
            </div>
        </div>

        <div class="flex-grow-1 d-flex flex-column justify-content-start align-items-center pt-5">

            <div class="text-center mb-5">
                <h1 class="fw-bold display-5">LABAssistance</h1>
                <p class="text-muted fs-6">Laundry Management System</p>
            </div>

            <div class="text-center mb-5">
                <h2 class="fw-bold fs-3">We Are:</h2>
                <h1 class="fw-bold status-text text-success">OPEN</h1>
            </div>

            <div class="w-100 text-center px-4">
                <a href="order.php" class="btn btn-dark btn-lg w-100 py-3 rounded-3 shadow-sm" style="max-width: 400px;">
                    Place New Order
                </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>