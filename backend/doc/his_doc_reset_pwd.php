<?php
session_start();
include('assets/inc/config.php');

// Xử lý khi người dùng submit form
if (isset($_POST['reset_pwd'])) {
    $email = $mysqli->real_escape_string($_POST['email']);

    // Kiểm tra xem email có tồn tại trong bảng his_doc không
    $check_query = "SELECT * FROM his_docs WHERE doc_email = ?";
    if ($check_stmt = $mysqli->prepare($check_query)) {
        $check_stmt->bind_param('s', $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Email tồn tại, tiến hành đặt lại mật khẩu
            $length_pwd = 10;
            $length_token = 30;
            $temp_pwd = substr(str_shuffle('0123456789QWERTYUIOPPLKJHGFDSAZCVBNMqwertyuioplkjhgfdsazxcvbnm'), 1, $length_pwd);
            $_token = substr(str_shuffle('0123456789QWERTYUIOPPLKJHGFDSAZCVBNMqwertyuioplkjhgfdsazxcvbnm'), 1, $length_token);

            $token = sha1(md5($_token));
            $status = "Pending";
            $pwd = password_hash($temp_pwd, PASSWORD_DEFAULT); // hash the temporary password

            // SQL to insert captured values
            $query = "INSERT INTO his_pwdresets (email, token, s_status, pwd) VALUES (?, ?, ?, ?)";
            if ($stmt = $mysqli->prepare($query)) {
                $stmt->bind_param('ssss', $email, $token, $status, $pwd);
                $stmt->execute();

                // Check if insertion was successful
                if ($stmt->affected_rows > 0) {
                    $success = "Check your inbox for password reset instructions";

                    // Call the Python script
                    $mail = escapeshellarg($email);  // Escape and quote email variable
                    $pws = escapeshellarg($temp_pwd);  // Escape and quote temporary password variable
                    $token = escapeshellarg($_token);  // Escape and quote token variable

                    // Adjust Python script path and Python command as needed
                    $command = escapeshellcmd("python -u c:\\xampp\\htdocs\\HIS\\backend\\doc\\send_mail.py $mail $pws $token");
                    $output = shell_exec($command);

                    // Log or handle any output or errors from the Python script
                    if ($output === false) {
                        $err = "Failed to execute Python script";
                        // Log error here if needed
                    } else {
                        echo "Python script output: <pre>$output</pre>";
                    }
                } else {
                    $err = "Failed to insert data into database";
                    // Log error here if needed
                }

                // Close statement
                $stmt->close();
            } else {
                $err = "Failed to prepare insert statement: " . $mysqli->error;
            }
        } else {
            // Email không tồn tại
            $err = "Email không tồn tại hoặc chưa được đăng ký.";
        }

        // Close check statement
        $check_stmt->close();
    } else {
        $err = "Failed to prepare check statement: " . $mysqli->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Hospital Management Information System -A Super Responsive Information System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- App css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <!--Load Sweet Alert Javascript-->
    <script src="assets/js/swal.js"></script>
    <!--Inject SWAL-->
    <?php if (isset($success)) { ?>
        <!--This code for injecting an alert-->
        <script>
            setTimeout(function() {
                    swal("Success", "<?php echo $success; ?>", "success");
                },
                100);
        </script>

    <?php } ?>

    <?php if (isset($err)) { ?>
        <!--This code for injecting an alert-->
        <script>
            setTimeout(function() {
                    swal("Failed", "<?php echo $err; ?>", "Failed");
                },
                100);
        </script>

    <?php } ?>



</head>

<body class="authentication-bg authentication-bg-pattern">

    <div class="account-pages mt-5 mb-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card bg-pattern">

                        <div class="card-body p-4">

                            <div class="text-center w-75 m-auto">
                                <a href="his_doc_reset_pwd.php">
                                    <span><img src="assets/images/logo-dark.png" alt="" height="22"></span>
                                </a>
                                <p class="text-muted mb-4 mt-3">Enter your email address and we'll send you an email with instructions to reset your password.</p>
                            </div>

                            <form method="post">

                                <div class="form-group mb-3">
                                    <label for="emailaddress">Email address</label>
                                    <input class="form-control" name="email" type="email" id="emailaddress" required="" placeholder="Enter your email">
                                </div>
                                <div class="form-group mb-3" style="display:none">
                                    <label for="emailaddress">Reset Token</label>
                                    <input class="form-control" name="token" type="text" value="<?php echo $_token; ?>">
                                </div>
                                <div class="form-group mb-3" style="display:none">
                                    <label for="emailaddress">Reset Temp Pwd</label>
                                    <input class="form-control" name="pwd" type="text" value="<?php echo $temp_pwd; ?>">
                                </div>
                                <div class="form-group mb-3" style="display:none">
                                    <label for="emailaddress">Status</label>
                                    <input class="form-control" name="status" type="text" id="emailaddress" required="" value="Pending">
                                </div>

                                <div class="form-group mb-0 text-center">
                                    <button name="reset_pwd" class="btn btn-primary btn-block" type="submit"> Reset Password </button>
                                </div>

                            </form>

                        </div> <!-- end card-body -->
                    </div>
                    <!-- end card -->

                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <p class="text-white-50">Back to <a href="index.php" class="text-white ml-1"><b>Log in</b></a></p>
                        </div> <!-- end col -->
                    </div>
                    <!-- end row -->

                </div> <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end page -->


    <?php include("assets/inc/footer1.php"); ?>

    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.min.js"></script>

</body>

</html>