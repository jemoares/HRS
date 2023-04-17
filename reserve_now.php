<?php 

    require('admin/inc/db_config.php');
    require('admin/inc/essentials.php');
    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/SMTP.php';
    require 'PHPMailer/Exception.php';


    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    date_default_timezone_set("Asia/Manila");

    session_start();
    
   
    if(isset($_POST['reserve_now']))
    {

        // Retrieve form data
        $recipient = $_POST['email']; // Email address of the recipient
        
        // $price = $_GET['price'];
        $CUST_ID = $_SESSION['uId'];
        $ORDER_ID = 'ORD_'.$_SESSION['uId'].random_int(11111,9999999);
        $TXN_AMOUNT = $_SESSION['room']['payment'];
        $rtype = $_SESSION['room']['name'];
        $rprice = $_SESSION['room']['price'];
        // $date = $_SESSION['datentime'];
        $frm_data = filteration($_POST);

        $query1 = "INSERT INTO `booking_order`(`user_id`, `room_id`, `check_in`, `check_out`, `trans_amt`, `order_id`) 
        VALUES (?,?,?,?,?,?)";

        insert($query1,[$CUST_ID,$_SESSION['room']['id'],$frm_data['checkin'],
            $frm_data['checkout'],$TXN_AMOUNT,$ORDER_ID],'isssss');

        $booking_id = mysqli_insert_id($con);
        
        $query2 = "INSERT INTO `booking_details`(`booking_id`, `room_name`, `price`, 
        `total_pay`, `user_name`, `phonenum`, `address`) VALUES (?,?,?,?,?,?,?)";

        insert($query2,[$booking_id,$_SESSION['room']['name'],$_SESSION['room']['price'],
        $TXN_AMOUNT,$frm_data['name'],$frm_data['phonenum'],$frm_data['address']],'issssss');
        

        
            // Create a new PHPMailer instance
            $mail = new PHPMailer(true);

            try {
                // Configure SMTP settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // SMTP host of your email provider
                $mail->SMTPAuth = true;
                // $mail->Username = 'hotelmokko32@gmail.com'; // Your email address
                // $mail->Password = 'wnpbjlvgavfalnqk'; // Your email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587; // Port number for SMTP
    
                // Recipients
                $mail->setFrom('hotelmokko32@gmail.com', 'Hotel Mokko'); // Your email address and name
                $mail->addAddress($recipient); // Recipient's email address
    
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your Reservation Receipt Details';
                $mail->Body    = "
                    Dear Valued Guest, <br><br> 

                    We are glad to inform you that your room reservation is now confirmed, You may present this message or the PDF Receipt that can be seen and download in your profile<br>
                    to the Front Office personnel upon your arrival at the Hotel Mokko.<br><br>

                    Reservation Details: <br>
                    Reservation ID: $ORDER_ID<br>
                    Date of Check in: $frm_data[checkin]<br>
                    Date of Check out: $frm_data[checkout]<br>
                    Room type: $rtype<br>
                    Rate: ₱$rprice Per hour<br>
                    Name of Guest: $frm_data[name]<br>
                    Phone Number: $frm_data[phonenum]<br>
                    Amount to pay: ₱$TXN_AMOUNT<br><br>
                    
                    Note: Please be reminded that your reservation may not be honored if you do not show up 30 minutes after your given time of arrival.
                    You may also check out earlier than the scheduled check-out time, <br>
                    but kindly note that the whole rate of 24 hours should still be paid. Thank you and we look forward to your arrival at Hotel Mokko. <br>
   
                ";
    
                // Send the email
                $mail->send();
                echo "<script>alert('Reservation Successfully, Receipt is sent to your email');
                        window.location.href='bookings.php';
                    </script>
                ";
            } catch (Exception $e) {
                echo "<script>alert('Email could not be sent. Error: ');
                        window.location.href='rooms.php';
                    </script>
                ", $mail->ErrorInfo;
            }

        // echo "
        //     <script> alert('Reservation Successfully');
        //         window.location.href='bookings.php';
        //     </script>
        
        // ";
        
    }

    // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //     if (isset($_POST['emailCheckbox']) && $_POST['emailCheckbox'] === 'on') {
    //         // Get the logged-in user's email address (replace with your own logic)
    //         $loggedInUserEmail = $frm_data['email'];
    //         echo $loggedInUserEmail;
    //         // Send an email to the logged-in user
    //         $to = $loggedInUserEmail;
    //         $subject = 'Email Checkbox Example';
    //         $message = 'This is a test email sent from the email checkbox example.';
    //         $headers = 'From: hotelmokko32@gmail.com' . "\r\n" .
    //             'Reply-To: hotelmokko32@gmail.com' . "\r\n" .
    //             'X-Mailer: PHP/' . phpversion();
            
    //         if (mail($to, $subject, $message, $headers)) {
    //             echo 'Email sent successfully';
    //         } else {
    //             echo 'Failed to send email';
    //         }
    //     }
    // }

    
?>

<!-- <html>
    <head>
        <title>Processing</title>
    </head>
    <body>
        
        <h1>Please do not refresh this page...</h1>

        <form action="" method="post"></form>


    </body>
</html> -->