<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require('inc/links.php'); ?>
    <title><?php echo $settings_r['site_title'] ?> - CONFIRM BOOKING</title>
</head>

<body class="bg-light">

    <?php require('inc/header.php');
    
    // $checkin_default="";
    // $checkout_default="";

    // if(isset($_GET['check_availability']))
    // {
    //     $frm_data = filteration($_GET);

    //     $checkin_default=$frm_data['checkin'];
    //     $checkout_default=$frm_data['checkout'];
    // }
    

    /*
        CHECK ROOM SA URL KUNG ACTIVE BA OR HINDI
        Shutdown mode is active or hindi
        User is logged in or hindi

    */
    if (!isset($_GET['id']) || $settings_r['shutdown'] == true) {
        redirect('rooms.php');
    } else if (!(isset($_SESSION['login']) && $_SESSION['login'] == true)) {
        redirect('rooms.php');
    }

    //filter sa pagkuha ng room tsaka ng customer data

    $data = filteration($_GET);

    $room_res = select("SELECT * FROM `rooms` WHERE `id`=? AND `status`=? AND `removed`=?", [$data['id'], 1, 0], 'iii');

    if (mysqli_num_rows($room_res) == 0) {
        redirect('rooms.php');
    }

    $room_data = mysqli_fetch_assoc($room_res);

    $_SESSION['room'] = [
        "id" => $room_data['id'],
        "name" => $room_data['name'],
        "price" => $room_data['price'],
        "payment" => null,
        "available" => false,
    ];

    $user_res = select("SELECT * FROM `user_cred` WHERE `id` = ? LIMIT 1", [$_SESSION['uId']], "i");
    $user_data = mysqli_fetch_assoc($user_res);

    ?>



    <div class="container">
        <div class="row">
            <div class="col-12 my-5 px-4">
                <h2 class="fw-bold">CONFIRM BOOKING</h2>
                <div style="font-size: 14px;">
                    <a href="index.php" class="text-secondary text-decoration-none">HOME</a>
                    <span class="text-secondary"> > </span>
                    <a href="rooms.php" class="text-secondary text-decoration-none">ROOMS</a>
                    <span class="text-secondary"> > </span>
                    <a href="#" class="text-secondary text-decoration-none">CONFIRM</a>
                </div>
            </div>

            <div class="col-lg-7 col-md-12 px-4">
                <?php
                $room_thumb = ROOMS_IMG_PATH . "thumbnail.jpg";
                $thumb_query = mysqli_query($con, "SELECT * FROM `room_images` 
                 WHERE `room_id`='$room_data[id]' 
                 AND `thumb`='1'");

                if (mysqli_num_rows($thumb_query) > 0) {
                    $thumb_res = mysqli_fetch_assoc($thumb_query);
                    $room_thumb = ROOMS_IMG_PATH . $thumb_res['image'];
                }

                echo <<<data
                    <div class="card p-3 shadow-sm rounded">
                        <img src="$room_thumb" class="img-fluid rounded mb-3">
                        <h5>$room_data[name]</h5>
                        <h6>₱$room_data[price] Per hour</h6>
                    </div>
                 data;
                ?>
            </div>
            <div class="col-lg-5 col-md-12 px-4">
                <div class="card mb-4 border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <form action="reserve_now.php" method="POST" id="booking_form">
                            <h6 class=mb-3>BOOKING DETAILS</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Name:</label>
                                    <input name="name" type="text" value="<?php echo $user_data['name'] ?>" class="form-control shadow-none" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number:</label>
                                    <input name="phonenum" type="number" value="<?php echo $user_data['phonenum'] ?>" class="form-control shadow-none" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Address:</label>
                                    <textarea name="address" class="form-control shadow-none" rows="1" required><?php echo $user_data['address'] ?></textarea>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Email:</label>
                                    <input name="email" type="text" value="<?php echo $user_data['email'] ?>" class="form-control shadow-none" required> 
                                </div>
                                <div class="col-md-12">
                                    <!-- <label class="form-label">Time of arrival:</label> -->
                                    <label class="form-label text-danger" style="font-size: 13px;">(Note: Your reservation will be cancelled if you don't appear on the day you reserve)</label>
                                    <!-- <input name="arrival"  type="time" class="form-control shadow-none" style="width: 223px;"> -->
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Check-in</label>
                                    <!-- <input name="checkin" onchange="check_availability()" id="checkin" type="date" min="<?php echo date("Y-m-d"); ?>" class="form-control shadow-none" required> -->
                                    <input name="checkin" onchange="check_availability()"  id="checkin" type="datetime-local" min="<?php echo date("Y-m-d"); ?>" class="form-control shadow-none" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Check-out</label>
                                    <!-- <input name="checkout" onchange="check_availability()" id="checkout" type="date" min="<?php echo date("Y-m-d"); ?>" class="form-control shadow-none" required> -->
                                    <input name="checkout" onchange="check_availability()" value="<?php echo $checkout_default ?>" id="checkout" type="datetime-local" min="<?php echo date("Y-m-d"); ?>" class="form-control shadow-none" required>
                                </div>
                                <!-- <div class="col-md-7 mb-3">
                                <form action="send_email.php" method="post">
                                    <input type="checkbox" id="emailCheckbox" name="emailCheckbox" required>
                                    <label for="emailCheckbox">Send receipt to your email</label>
                                </form>
                                </div> -->
                                <div class="col-12">
                                    <div class="spinner-border text-info mb-3 d-none" id="info_loader" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>

                                    <h6 class="mb-3 text-danger" id="pay_info">Provide check-in & check-out date</h6>

                                    <button name="reserve_now" class="btn w-100 text-white custom-bg shadow-none mb-1" disabled>
                                        Reserve Now
                                    </button>
                                    <!-- <div id="paypal-button-container"></div> -->
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>

    <!-- <script src="https://www.paypal.com/sdk/js?client-id=AcWN7JgN-pLzDbmfeuYwjiFD72twVN2CrHEyzb0_h3CtNdU355LrgwJnd3TA0z1TCGpMFHVLpnaWawF1&currency=USD"></script>
    <script>
       paypal.Buttons({
        // Sets up the transaction when a payment button is clicked
        createOrder: (data, actions) => {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: data.payment // Can also reference a variable or function
                    }
                }]
            });

        },
        // Finalize the transaction after payer approval
        onApprove: (data, actions) => {
            return actions.order.capture().then(function(orderData) {
                //Successful capture for dev/demo purposes:
                console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));
                const transaction = orderData.purchase_units[0].payments.captures[0];
                alert(`Transaction ${transaction.status}: ${transaction.id}\n\nSee console for all available detail`);
            });
        }
       }).render('#paypal-button-container');
    </script> -->

    <script>
        let booking_form = document.getElementById('booking_form');
        let info_loader = document.getElementById('info_loader');
        let pay_info = document.getElementById('pay_info');

        function check_availability() {
            let checkin_val = booking_form.elements['checkin'].value;
            let checkout_val = booking_form.elements['checkout'].value;


            booking_form.elements['reserve_now'].setAttribute('disabled', true);

            if (checkin_val != '' && checkout_val != '') 
            {

                pay_info.classList.add('d-none');
                pay_info.classList.replace('text-dark','text-danger');
                info_loader.classList.remove('d-none');

                let data = new FormData();

                data.append('check_availability', '');
                data.append('check_in', checkin_val);
                data.append('check_out', checkout_val);

                let xhr = new XMLHttpRequest();
                xhr.open("POST", "ajax/confirm_booking.php", true);

                xhr.onload = function() {
                    let data = JSON.parse(this.responseText);
                    if(data.status == 'check_in_out_equal')
                    {
                        pay_info.innerText = "You cannot-check out on the same day";
                    }
                    else if(data.status == 'check_out_earlier')
                    {
                        pay_info.innerText = "Check-out date is earlier than check-in date";
                    }
                    else if(data.status == 'check_in_earlier')
                    {
                        pay_info.innerText = "Check-in date is earlier than today's date";
                    }
                    else if(data.status == 'unavailable')
                    {
                        pay_info.innerText = "Room not available for this check-in date";
                    }
                    else
                    {
                        pay_info.innerHTML = "Total Amount to Pay: ₱"+data.payment;
                        // pay_info.innerHTML = "Number of Days: "+data.days+"<br>Total Amount to Pay: ₱"+data.payment;
                        pay_info.classList.replace('text-danger','text-dark');
                        booking_form.elements['reserve_now'].removeAttribute('disabled');
                    }

                    pay_info.classList.remove('d-none');
                    info_loader.classList.add('d-none');
                }

                xhr.send(data);
            }
        }

        // document.getElementById('emailCheckbox').addEventListener('change', function() {
        //     if (this.checked) {
        //         // Submit the form when checkbox is checked
        //         this.closest('form').submit();
        //     }
        // });
    </script>
</body>

</html>