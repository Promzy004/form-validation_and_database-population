<?php

    $pagetitle = "Reset password";
    require_once 'assets/header.php';
    require_once 'assets/db_connect.php';
    require_once 'assets/mailer.php';



    $token = $_GET['token'];
    $passwordError = "";
    $verification_error = "";

    if(isset($_POST['submit'])) {

        $password = htmlspecialchars(trim($_POST['pass']));
        $cpassword = htmlspecialchars(trim($_POST['cpass']));

        // Validating Password and Confirm Password
        if ($password == $cpassword) {
            if(preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])[a-zA-Z0-9]{8,}$/', $password)){
                $hashpassword = password_hash($password, PASSWORD_DEFAULT);
            }else{
                $passwordError = "Password must contain at least one Uppercase, Lowercase, and
                number with at least 8 characters long";
            }
        }else {
            $passwordError = "password does not match";
        }


        $query = "SELECT * FROM users WHERE verification_code = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $sql = "UPDATE users SET verification_code = NULL, password = ?, verified = 1 WHERE verification_code = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss",$hashpassword, $token);
            if($stmt->execute()) {
                header("Location: reset_successful.php");
                exit();
            } else {
                $verification_error = "Verification failed Please contact the administrator via <a href='mailto:nh@roncloud.com.ng'>nh@roncloud.com.ng</a>";
            }
        }
    }
?>

<form method="POST" class="w-50 bg-success mx-auto my-5 d-flex flex-column text-center pt-3 pb-5 px-5 gap-4">
    <h2>Fill the form</h2>
    <div class="w-100">
        <input type="password" name="pass" placeholder="Enter new Password" class="w-100">
        <small class="text-danger"><?= $passwordError ?> </small>
    </div>
    <div class="w-100">
        <input type="password" name="cpass" placeholder="Confirm Password" class="w-100">
        <small class="text-danger"><?= $passwordError ?> </small>
    </div>
    <input type="submit" name="submit" class="btn btn-primary col-3 d-flex align-self-center">
    <h3 class="text-danger"> <?= $verification_error ?> </h3>
</form>