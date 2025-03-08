<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'vendor/autoload.php';

    $mail = new PHPMailer(true);
    try {
      // SMTP server settings
      $mail->isSMTP();
      $mail->Host = 'mail.roncloud.com.ng';
      $mail->SMTPAuth = true;
      $mail->Username = 'nh@roncloud.com.ng';
      $mail->Password = 'Estherblish';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $mail->Port = 465;
    } catch(\Exception $e){
        die($e->getMessage());
        //die("Mail not sent: ($mail->ErrorInfo)");
    }
?>

  