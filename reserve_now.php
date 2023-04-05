<?php 

    require('admin/inc/db_config.php');
    require('admin/inc/essentials.php');


    date_default_timezone_set("Asia/Manila");

    session_start();


    if (!(isset($_SESSION['login']) && $_SESSION['login'] == true)) {
        redirect('index.php');
    }

    if(isset($_POST['reserve_now']))
    {
        // $price = $_GET['price'];
        $CUST_ID = $_SESSION['uId'];
        $ORDER_ID = 'ORD_'.$_SESSION['uId'].random_int(11111,9999999);
        $TXN_AMOUNT = $_SESSION['room']['payment'];
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



        echo "
            <script> alert('Reservation Successfully');
                window.location.href='bookings.php';
            </script>
        
        ";
        

        
    }

    
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