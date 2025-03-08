<?php 
 $pagetitle = "Sign Up";
 require_once 'assets/header.php';
 require_once 'assets/db_connect.php';
 require_once 'assets/mailer.php';
 
 // Initializing Variables
 $msg = "Registration";
 $firstname = $lastname = $email = $tel = "";
 $emailError = $passwordError = $phoneError = "";


 //Capturing your entries
    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
      $file = $_FILES['user_image'];
      $firstname = htmlspecialchars(trim($_POST['firstname']));
      $lastname = htmlspecialchars(trim($_POST['lastname']));
      $email = htmlspecialchars(trim($_POST['email']));
      $password = htmlspecialchars(trim($_POST['password']));
      $cpassword = htmlspecialchars(trim($_POST['cpassword']));
      $tel = htmlspecialchars(trim($_POST['tel']));
      $gender = htmlspecialchars(trim($_POST['gender']));
      $role = htmlspecialchars(trim($_POST['role']));
    

  // User Image Validation
  $filesize = 3 * 1024 * 1024;
  if($file['type'] == 'image/png' || $file['type'] == 'image/jpg' || $file['type'] == 'image/jpeg') {
    if($file['size'] <= $filesize) {
      $filename = uniqid('user_image_') . "." . pathinfo( $file['name'], 
      PATHINFO_EXTENSION);
      $fileLocation = "users_pictures/" . $filename;
      move_uploaded_file(from: $file['tmp_name'], to: $fileLocation);
      echo "Uploaded Successfully";
    } else {
      $fileLocation = "";
    }
    
  } else {
    $fileLocation = "";
  }
  
  // Validating Email Address
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $emailError = 'Email Address is not valid';
  }else{
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param( "s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
   if($result->num_rows > 0) {
      $emailError = "Phone number already exists";
    }
  }

  // Validating Password and Confirm Password
  if ($password == $cpassword) {
   if(preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])[a-zA-Z0-9]{8,}$/', 
   $password)){
    $hashpassword = password_hash($password, PASSWORD_DEFAULT);
   }else{
    $passwordError = "Password must contain at least one Uppercase, Lowercase, and
    number with at least 8 characters long";
   }
  } else {
   $passwordError = "password does not match";
  }

  // Validating Phone Number
  if (!preg_match('/^0[789][01]\d{8}$/', $tel)) {
    $phoneError = 'Invalid phone number';
  }else{
    $query = "SELECT * FROM users WHERE phone_number = ?";
    $stmt = $conn->prepare(query: $query);
    $stmt->bind_param("s", $tel);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $phoneError = "Phone number already exists";
    }
  }


   // Database Population
  if($emailError == "" && $phoneError == "" &&  $passwordError == ""){
    $verification_token = bin2hex( random_bytes( 32));
    $query = "INSERT INTO `users`(firstname, lastname, email, password, phone_number, gender, user_level, user_image, verification_code) VALUES (?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssss", $firstname, $lastname, $email, $hashpassword, $tel, $gender, $role, $fileLocation, $verification_token);
    if($stmt->execute()){
      
      // Send Verification Mail
      $verification_link = "http://localhost/reluxmain/verify.php?token=$verification_token";
      $fullname = $firstname . " " . $lastname;
      $mail->setFrom('nh@roncloud.com.ng', "Relux");
      $mail->addAddress($email, $fullname);
      $mail->isHTML(true);
      $mail->Subject = 'Relux User Verification';
      $mail->Body ="
          <h1>Dear, $fullname</h1>
          <p>Please click on the link below to verify your email address.</p>
          <a href='$verification_link'>Verify Email</a>
      ";
      $mail->AltBody = "Dear $fullname Please click on the link below to verify your 
      email address. $verification_link ";
      $mail->send();

      //Saving Upload file to Upload Folder
      move_uploaded_file( from: $file['tmp_name'], to: $fileLocation);
      $firstname = $lastname = $email = $tel = "";
      $msg = "<span class='text-success'>Registration Successful</span>";
    }else{
      $msg = "<span class='text-danger'>Registration Failed</span>";
    }
  } else {
    $msg = "<span class='text-danger'>Registration Failed</span>";
  }
   }
?>

<section class="h-100 bg-dark">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col">
        <div class="card card-registration my-4">
          <div class="row g-0">
            <div class="col-xl-6 d-none d-xl-block">
              <img src="./assets/images/Noir.jpeg"
                alt="Sample photo" class="img-fluid"
                style="border-top-left-radius: .25rem; border-bottom-left-radius: .25rem;" />
            </div>
            <div class="col-xl-6 bg-primary text-white">
              <form action="" method="post" enctype="multipart/form-data">
              <div class="card-body p-md-5 text-black">
                <!-- <h3 class="mb-5 text-uppercase">Registration</h3> -->
                <h3 class="mb-5 text-uppercase"><?= $msg ?></h3>
                <h1>
                  <?php 
                      echo "<h3> $verification_token </h3>"
                  ?>
                </h1>

                <!-- user Image Preview -->
                 <div class="form-outline mb-4">
                  <img class="form-control form-control-lg" alt="User Image"
                  src="users_pictures/avatar.jpeg" id="ImagePreview"/>
                  <label class="form-label" id="upload_label" >Upload User Image</label>
                  <input type="file" id="user_upload" class="form-control"
                  form-control-lg name="user_image"/>
                 </div>


                <!-- Firstname & Lastname Capturing -->
                <div class="row">
                  <div class="col-md-6 mb-4">
                    <div data-mdb-input-init class="form-outline">
                      <input type="text" id="form3Example1m" placeholder="Firstname" class="form-control form-control-lg"
                          name="firstname" required />
                       <label class="form-label" for="form3Example1m">First name</label>
                    </div>
                  </div>
                  <div class="col-md-6 mb-4">
                    <div data-mdb-input-init class="form-outline">
                      <input type="text" id="form3Example1n" placeholder="Lastname" class="form-control form-control-lg" 
                        name="lastname" required />
                    </div>
                  </div>
                </div>
                  
                 <!-- E-Mail Capturing -->
                 <div data-mdb-input-init class="form-outline mb-4">
                  <input type="email" id="form3Example97" class="form-control 
                  form-control-lg" name="email" required />
                  <label class="form-label" for="form3Example97">Email ID</label>
                  <span class="text-danger"><?= $emailError ?></span>
                </div>

                <!-- Password & Confirm Password Capturing -->
                <div class="row">
                  <div class="col-md-6 mb-4">
                    <div data-mdb-input-init class="form-outline">
                      <input type="password" id="form3Example1m" class="form-control form-control-lg"
                          name="password" required />
                      <label class="form-label" for="form3Example1m">Password</label>
                      <span class="text-danger"><?= $passwordError ?></span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-4">
                    <div data-mdb-input-init class="form-outline">
                      <input type="password" id="form3Example1n" class="form-control form-control-lg" 
                        name="cpassword" required/>
                      <label class="form-label" for="form3Example1n">Confirm Password</label>
                      <span class="text-danger"><?= $passwordError ?></span>
                    </div>
                  </div>
                </div>

                <!-- Phone Number Capturing -->
                <div data-mdb-input-init class="form-outline">
                      <input type="tel" id="form3Example1m" class="form-control form-control-lg"
                          name="tel" required/>
                      <label class="form-label" for="form3Example1m">Phone Number</label>
                      <span class="text-danger"><?= $phoneError ?></span>
                    </div>

                      <!-- Gender Capturing    -->
                <div class="d-md-flex justify-content-start align-items-center mb-4 py-2">

                  <h6 class="mb-0 me-4">Gender: </h6>

                  <div class="form-check form-check-inline mb-0 me-4">
                    <input class="form-check-input" type="radio" 
                      value="Male" name="gender" />
                    <label class="form-check-label" for="MaleGender">Male</label>
                  </div>

                  <div class="form-check form-check-inline mb-0 me-4">
                    <input class="form-check-input" type="radio"  id="FemaleGender"
                      value="Female" name="gender" />
                    <label class="form-check-label" for="FemaleGender">Female</label>
                  </div>

                  <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="radio"  id="otherGender"
                      value="Other" name="gender" />
                    <label class="form-check-label" for="otherGender">Other</label>
                  </div>

                </div>
               
                <!-- User Level Capturing -->
                <div class="d-md-flex justify-content-start align-items-center mb-4 py-2">

                  <h6 class="mb-0 me-4">Role: </h6>

                  <div class="form-check form-check-inline mb-0 me-4">
                    <input class="form-check-input" type="radio" 
                      value="user" name="role" />
                    <label class="form-check-label" >User</label>
                  </div>

                  <div class="form-check form-check-inline mb-0 me-4">
                    <input class="form-check-input" type="radio"
                      value="vendor" name="role" />
                    <label class="form-check-label" for="vendor">Vendor</label>
                  </div>


                </div>
                


                <div class="d-flex justify-content-end align-items-center gap-2 pt-3">
                  <a href="forgotten.php" class="text-black">forgot password?</a>
                  <button  type="button" data-mdb-button-init data-mdb-ripple-init class="btn btn-light btn-lg">Reset all</button>
                  <input  type="submit" data-mdb-button-init data-mdb-ripple-init
                   class="btn btn-warning btn-lg ms-2" value="Register">
                </div>

              </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>