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
    <!-- HEADER -->

    <!-- MAIN -->
    <!-- REGISTER -->
    <div class="d-flex justify-content-center">
        <div class="w-50" id="register">
            <div class="row border border-black">
                <label>Register</label>
            </div>

            <div class="row border border-black">
                <div class="row border border-black">
                    <label class="mt-3">Full Name</label>
                    <input type="text" name="full_name">
                </div>

                <div class="row border border-black">
                    <label class="mt-3">Email</label>
                    <input type="text" name="username">
                </div>

                <div class="row border border-black">
                    <label class="mt-3">Password</label>
                    <input type="password" name="email">
                </div>

                <div class="row border border-black">
                    <label class="mt-3">Confirm Password</label>
                    <input type="password" name="password">
                </div>

                <div class="row border border-black">
                    <input type="submit" class="mt-3 btn btn-dark w-50" name="register" value="Register">
                </div>
                <div class="row border border-black">
                    <button class="mt-3 btn btn-dark w-50" onclick="showLogin()">Already have an account? Login</button>
                </div>
            </div>
        </div>


        <!-- LOGIN -->
        <div class="w-50" id="login" style="display: none">
            <div class="row border border-black">
                <label>Log in</label>
            </div>

            <div class="row border border-black">

                <div class="row border border-black">
                    <label class="mt-3">Email</label>
                    <input type="text" name="username">
                </div>

                <div class="row border border-black">
                    <label class="mt-3">Password</label>
                    <input type="text" name="email">
                </div>

                <div class="row border border-black">
                    <a class="mt-3 btn btn-dark w-50" href="dashboard.php">
                        <input class=" btn btn-dark" type="submit" name="signin" value="Sign in">
                    </a>
                </div>
                <div class="row border border-black">
                    <button class="mt-3 btn btn-dark w-50" onclick="showPassRecovery()">Forgot Password?</button>
                </div>
                <div class="row border border-black">
                    <button class="mt-3 btn btn-dark w-50" onclick="showRegister()">Don't have an account? Register</button>
                </div>
            </div>
        </div>

        <!-- Password Recovery -->
        <div class="w-50" id="passRecovery" style="display: none">
            <div class="row border border-black">
                <label>Reset Password</label>
            </div>

            <div class="row border border-black">
                <div class="row border border-black">
                    <label class="mt-3">Email</label>
                    <input type="text" name="username">
                </div>

                <div class="row border border-black">
                    <button class="mt-3 btn btn-dark w-50">Send Recovery Link</button>
                </div>
                <div class="row border border-black">
                    <button class="mt-3 btn btn-dark w-50" onclick="showLogin()">Back to Login</button>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/scripts.js"></script>
</body>

</html>