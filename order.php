<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Place Order - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/design.css">
    <link rel="stylesheet" href="./css/dashboard.css">
</head>

<body class="bg-light">

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">

                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-4">

                        <div class="text-center mb-4">
                            <h2 class="fw-bold">Place an Order</h2>
                            <span class="badge bg-warning text-dark fw-normal px-3 py-2 rounded-pill">1 Load = Min 3kg, Max 8kg</span>
                        </div>

                        <form action="backend/process_order.php" method="POST" id="orderForm">

                            <h5 class="fw-bold mb-3 small text-uppercase text-muted ls-1">Service Type</h5>
                            <div class="mb-4">
                                <label class="service-card p-3 border rounded mb-2 d-flex align-items-center bg-white cursor-pointer">
                                    <input class="form-check-input me-3" type="checkbox" id="checkWash" name="checkWash" value="50">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">Wash</div>
                                    </div>
                                    <span class="fw-bold text-dark">₱50<span class="text-muted small fw-normal">/load</span></span>
                                </label>

                                <label class="service-card p-3 border rounded mb-2 d-flex align-items-center bg-white cursor-pointer">
                                    <input class="form-check-input me-3" type="checkbox" id="checkDry" name="checkDry" value="60">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">Dry</div>
                                    </div>
                                    <span class="fw-bold text-dark">₱60<span class="text-muted small fw-normal">/load</span></span>
                                </label>

                                <label class="service-card p-3 border rounded mb-2 d-flex align-items-center bg-white cursor-pointer">
                                    <input class="form-check-input me-3" type="checkbox" id="checkFold" name="checkFold" value="35">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">Fold</div>
                                    </div>
                                    <span class="fw-bold text-dark">₱35<span class="text-muted small fw-normal">/load</span></span>
                                </label>
                            </div>

                            <div id="errorWetClothes" class="alert alert-danger d-none animate-fade" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i> Wet clothes cannot be folded!
                            </div>

                            <div id="suppliesSection" class="mb-4">
                                <h5 class="fw-bold mb-3 small text-uppercase text-muted ls-1" id="suppliesHeader">Laundry Supplies (For Wash)</h5>

                                <label class="service-card p-3 border rounded mb-2 d-flex align-items-center bg-white cursor-pointer">
                                    <input class="form-check-input me-3" type="checkbox" id="supplyDetergent" name="supplyDetergent" value="20" disabled>
                                    <div class="flex-grow-1">Detergent</div>
                                    <span class="text-muted small">₱20/load</span>
                                </label>

                                <label class="service-card p-3 border rounded mb-2 d-flex align-items-center bg-white cursor-pointer">
                                    <input class="form-check-input me-3" type="checkbox" id="supplySoftener" name="supplySoftener" value="10" disabled>
                                    <div class="flex-grow-1">Fabric Softener</div>
                                    <span class="text-muted small">₱10/load</span>
                                </label>
                            </div>

                            <h5 class="fw-bold mb-2 mt-4 small text-uppercase text-muted ls-1">Load Quantity</h5>
                            <p class="text-muted small mb-3" style="font-size: 0.8rem;">Enter bags per category. 1 bag = 1 load.</p>

                            <div id="standardInputs" class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="fw-bold small mb-1">Colored</label>
                                    <input type="number" id="qtyColored" name="qtyColored" class="form-control text-center py-3 fs-5 fw-bold" value="0" min="0" inputmode="numeric" pattern="[0-9]*">
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold small mb-1">White</label>
                                    <input type="number" id="qtyWhite" name="qtyWhite" class="form-control text-center py-3 fs-5 fw-bold" value="0" min="0" inputmode="numeric" pattern="[0-9]*">
                                </div>
                            </div>

                            <div id="foldOnlyInput" class="mb-3 d-none">
                                <label class="fw-bold small mb-1">Load For Folding</label>
                                <input type="number" id="qtyFold" name="qtyFold" class="form-control text-center py-3 fs-5 fw-bold" value="0" min="0" inputmode="numeric" pattern="[0-9]*">
                            </div>

                            <h5 class="fw-bold mb-2 mt-4 small text-uppercase text-muted ls-1">Note to Employee</h5>
                            <textarea class="form-control mb-4" name="note" rows="3" placeholder="Ex: Keep uniforms inside net"></textarea>

                            <div class="border-top pt-4 mt-4">
                                <h4 class="fw-bold">Total Payment</h4>
                                <h1 class="fw-bold display-4 mb-1 text-dark" id="totalPrice">₱0.00*</h1>
                                <p class="text-muted small fst-italic mb-4">Payment made in-shop.</p>
                            </div>

                            <div class="d-grid gap-3 pt-2">
                                <button type="submit" id="btnPlaceOrder" class="btn btn-dark py-3 fw-bold fs-5 shadow-sm" disabled>Place Order</button>
                                <a href="dashboard.php" class="btn btn-outline-secondary py-2 border-0">Cancel</a>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/order.js"></script>
</body>

</html>