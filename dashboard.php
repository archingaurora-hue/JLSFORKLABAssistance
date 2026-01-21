<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <!-- MAIN -->
    <a class="mt-3 btn btn-dark w-50" href="login.php">
        <input class="btn btn-dark" name="logout" value="Log Out">
    </a>

    <button class="mt-3 btn btn-dark w-50" onclick="showNewOrder()">Place New Order</button>

    <a class="mt-3 btn btn-dark w-50" href="login.php">
        <input class="btn btn-dark" name="history" value="history">
    </a>

    <!-- New Order -->
    <div class="border border-2 w-50" id="newOrder" style="display: none">
        <h2>Place an Order</h2>
        <h5>1 Load = Min 3kg, Mac 8kg</h5>

        <h5 class="mt-3">Service Type</h5>
        <div class="border border-black">
            <input type="checkbox" name="wash" value="wash">
            <label for="vehicle1">Wash (₱50/load)</label><br>
        </div>
        <div class="border border-black">
            <input type="checkbox" name="dry" value="dry">
            <label for="vehicle1">Dry (₱60/load)</label><br>
        </div>
        <div class="border border-black">
            <input type="checkbox" name="fold" value="fold">
            <label for="vehicle1">Fold (₱35/load)</label><br>
        </div>

        <h5 class="mt-3">Additional Laundry Supplies (for Wash)</h5>
        <div class="border border-black">
            <input type="checkbox" name="detergent" value="detergent">
            <label for="vehicle1">Detergent (₱20/load)</label><br>
        </div>
        <div class="border border-black">
            <input type="checkbox" name="softener" value="softener">
            <label for="vehicle1">Fabric Softener (₱60/load)</label><br>
        </div>

        <h5 class="mt-3">Load Quantity</h5>
        <h6>Enter the number of bags/basket per category</h6>
        <h6>1 bag/basket = 1 load</h6>
        <div class="d-flex gap-5">
            <div class="row w-25">
                <label>Colored</label>
                <input type="number">
            </div>
            <div class="row w-25">
                <label>White</label>
                <input type="number">
            </div>
        </div>

        <div class="row border border-black">
            <label class="mt-3">Note to Employee</label>
            <textarea name="note" placeholder="Example: Keep uniforms inside net"></textarea>
        </div>

        <h2>Total Payment</h2>
        <h2>₱0</h2>
        <h6>Payment to be made in-shop after order completion</h6>
        <h6 class="border border-black">*Note: When weight exceeds the maximum for each load (8kg), the excess laundry will be counted towards a new load and final payment will be adjusted accordingly</h6>

        <button type="submit" class="mt-3 btn btn-dark w-75">Place Order</button>

        <button class="mt-3 btn btn-dark w-75" onclick="hideNewOrder()">Cancel</button>
    </div>

    <!-- FOOTER -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/scripts.js"></script>
</body>

</html>