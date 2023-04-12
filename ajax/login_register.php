<?php

    require('../admin/inc/db_config.php');
    require('../admin/inc/essentials.php');
    // require('../inc/footer.php');
    date_default_timezone_set("Asia/Manila");
    // require("../inc/sendgrid/sendgrid-php.php");
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    
    
    function sendMail ($email,$token,$type)
    {
        require("../PHPMailer/PHPMailer.php");
        require("../PHPMailer/SMTP.php");
        require("../PHPMailer/Exception.php");

        $mail = new PHPMailer(true);

        if($type == "email_confirmation")
        {
            $page = 'email_confirm.php';
            $subject = "Account Verification Link";
            $content = "confirm your email";
        }
        else{
            $page = 'index.php';
            $subject = "Account Reset Link";
            $content = "reset your account";
        }

        try {
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            // $mail->Username   = 'hotelmokko32@gmail.com';                     //SMTP username
            // $mail->Password   = 'wxisjzsgemggbtgm';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
            //Recipients
            $mail->setFrom('hotelmokko32@gmail.com', 'Hotel Mokko');
            $mail->addAddress($email);     //Add a recipient
        
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = "Thank you for sending us your message.
            Click the link below to $content:
            <a href='".SITE_URL."$page?$type&email=$email&token=$token"."'>Click Me</a>";
        
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // function send_mail($uemail,$name,$token)
    // {
    //     $email = new \SendGrid\Mail\Mail(); 
    //     $email->setFrom("hotelmokko32@gmail.com", "Hotel Mokko");
    //     $email->setSubject("Account Verification Link");

    //     $email->addTo($uemail,$name);

    //     $email->addContent(
    //         "text/html", 
    //         "
    //             Click the link to confirm your email: <br>
    //             <a href='".SITE_URL."email_confirm.php?email_confirmation&email=$uemail&token=$token"."'>
    //                 CLICK ME
    //             </a>
    //         "
    //     );

    //     $sendgrid = new \SendGrid(SENDGRID_API_KEY);
        
    //     try{
    //         $sendgrid->send($email);
    //         return 1;
    //     }
    //     catch (Exception $e){
    //         return 0;
    //     }   
    // }



    if(isset($_POST['register']))
    {
        $data = filteration($_POST);

        //MATCH PASS AND CONFIRM PASS FIELD

        if($data['pass'] != $data['cpass']){
            echo 'pass_mismatch';
        }

        //CHECK USER EXISTS OR NOT

        $u_exist = select("SELECT * FROM `user_cred` WHERE `email` = ? OR `phonenum` = ? LIMIT 1",[$data['email'],$data['phonenum']],"ss");

        if(mysqli_num_rows($u_exist)!=0){
            $u_exist_fetch = mysqli_fetch_assoc($u_exist);
            echo ($u_exist_fetch['email'] == $data['email']) ? 'email_already' : 'phone_already';
            exit;
        }
        
        //UPLOAD USER IMAGE TO SERVER

        $img = uploadUserImage($_FILES['profile']);

        if($img == 'inv_img'){
            echo 'inv_img';
            exit;
        }
        else if($img == 'upd_failed'){
            echo 'upd_failed';
            exit;
        }

        //SEND CONFIRMATION LINK TO USER'S EMAIL

        $token = bin2hex(random_bytes(16));

        if(!sendMail($data['email'],$token, "email_confirmation")){
            echo 'mail_failed';
            exit;
        }

        $enc_pass = password_hash($data['pass'],PASSWORD_BCRYPT);

        $query = "INSERT INTO `user_cred`(`name`, `email`, `address`, `phonenum`, `pincode`, `dob`, 
        `profile`, `password`, `token`) VALUES (?,?,?,?,?,?,?,?,?)";

        $values = [$data['name'],$data['email'],$data['address'],$data['phonenum'],$data['pincode'],$data['dob'],
        $img,$enc_pass,$token];

        if(insert($query,$values,'sssssssss')){
            echo 1; 
        }else{
            echo 'ins_failed';
        }
       

    }

    
    if(isset($_POST['login']))
    {
        $data = filteration($_POST);

        $u_exist = select("SELECT * FROM `user_cred` WHERE `email` = ? OR `phonenum` = ? LIMIT 1",
        [$data['email_mob'],$data['email_mob']],"ss");

        if(mysqli_num_rows($u_exist)==0)
        {
            echo 'inv_email_mob';
            
        }
        else
        {
            $u_fetch = mysqli_fetch_assoc($u_exist);
            if($u_fetch['is_verified']==0){
                echo 'not_verified';
            }
            else if($u_fetch['status']==0){
                echo 'inactive';
            }
            else{
                if(!password_verify($data['pass'],$u_fetch['password'])){
                    echo 'invalid_pass';
                }
                else{
                    session_start();
                    $_SESSION['login'] = true;
                    $_SESSION['uId'] = $u_fetch['id'];
                    $_SESSION['uName'] = $u_fetch['name'];
                    $_SESSION['uPic'] = $u_fetch['profile'];
                    $_SESSION['uPhone'] = $u_fetch['phonenum'];
                    echo 1;
                }
            }
        }
    }

    if(isset($_POST['forgot_pass']))
    {
        $data = filteration($_POST);

        $u_exist = select("SELECT * FROM `user_cred` WHERE `email` = ? LIMIT 1",
        [$data['email']],"s");

        if(mysqli_num_rows($u_exist)==0)
        {
            echo 'inv_email';
        }
        else
        {
            $u_fetch = mysqli_fetch_assoc($u_exist);
            if($u_fetch['is_verified']==0){
                echo 'not_verified';
            }
            else if($u_fetch['status']==0){
                echo 'inactive';
            }
            else{
                // SEND RESET LINK TO EMAIL
                $token = bin2hex(random_bytes(16));
                if(!sendMail($data['email'],$token,'account_recovery')){
                    echo 'mail_failed';
                }
                else{
                    
                    $date = date("Y-m-d");
                    $query = mysqli_query($con, "UPDATE `user_cred` SET `token`='$token',`t_expire`='$date' 
                    WHERE `id`='$u_fetch[id]'");

                    if($query)
                    {
                        echo 1;
                    }
                    else
                    {
                        echo 'upd_failed';
                    }
                }

            }
        }

    }

    if(isset($_POST['recover_user']))
    {
        $data = filteration($_POST);

        $enc_pass = password_hash($data['pass'],PASSWORD_BCRYPT);

        $query = "UPDATE `user_cred` SET `password`=?, `token`=?, `t_expire`=? 
        WHERE `email`=? AND `token`=?";
        
        $values = [$enc_pass,null,null,$data['email'],$data['token']];

        if(update($query,$values,'sssss'))
        {
            echo 1;
        }
        else
        {
            echo 'failed';
        }
    }


?>