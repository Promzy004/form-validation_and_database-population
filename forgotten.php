<?php
    $pagetitle = "Recover password";
    require_once 'assets/header.php';
    require_once 'assets/db_connect.php';
    require_once 'assets/mailer.php';

    if(isset($_POST['submit'])) {
        $email = trim($_POST['email']);

        // Validating Email Address
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo 'Email Address is not valid';
        }else{
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param( "s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows == 1) {

                $verification_token = bin2hex( random_bytes( 32));

                $row = $result->fetch_assoc();
                $sql = "UPDATE users SET verification_code = ?, verified = 1 WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $verification_token, $email);


                if($stmt->execute()){
                    // Send Verification Mail
                    $resetpass_link = "http://localhost/reluxmain/reset.php?token=$verification_token";
                    $mail->setFrom('nh@roncloud.com.ng', "Relux");
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Relux User Verification';
                    $mail->Body ="
                        <p>Please use the link below to change your password</p>
                        <a href='$resetpass_link'>Reset Password</a>
                    ";
                    $mail->AltBody = "Please click on the link below to verify your 
                    email address. $resetpass_link ";
                    $mail->send();
                }
            } else{
                $msg = "You don't have an account";
            }
        }
    }
?>

<form method="POST" class="w-50 bg-success mx-auto my-5 d-flex flex-column text-center pt-3 pb-5 px-5 gap-4">
    <h2>Fill the form</h2>
    <div class="w-100 d-flex flex-column align-items-start">
        <input type="mail" name="email" placeholder="Enter Email" class="w-100">
        <small class="text-danger"> <?= $msg ?></small>
    </div>
    <input type="submit" name="submit" class="btn btn-primary col-3 d-flex align-self-center">
</form>