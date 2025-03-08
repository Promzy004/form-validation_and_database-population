<?php
    $pagetitle = "Verify your Email Address";
    require_once 'assets/header.php';
    require_once 'assets/db_connect.php';

    $token = $_GET['token'];

    $query = "SELECT * FROM users WHERE verification_code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $sql = "UPDATE users SET verification_code = NULL, verified = 1 WHERE verification_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        if($stmt->execute()) {
            echo "<h1> Verification Successful <h1/>";
        } else {
            echo "<h1>Verification Failed</h1>";
            echo "<p>Verification Failed</p>";
            echo "<h1>Please contact the administrator via <a href='mailto:nh@roncloud.com.ng'>nh@roncloud.com.ng</a></h1>";
        }
    }
?>

<p>
    <?php
        echo $token
    ?>
</p>