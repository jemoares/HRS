<?php

    require('../admin/inc/db_config.php');
    require('../admin/inc/essentials.php');
    date_default_timezone_set("Asia/Manila");

    session_start();
    
    $checkin_default="";
    $checkout_default="";

    if(isset($_GET['check_availability']))
    {
        $frm_data = filteration($_GET);

        $checkin_default=$frm_data['checkin'];
        $checkout_default=$frm_data['checkout'];
    }

    if (isset($_GET['fetch_rooms']))
    {

        // check availability data decode
        $chk_avail = json_decode($_GET['chk_avail'],true);

        // check in and check out validation 
        if($chk_avail['checkin']!='' && $chk_avail['checkout']!='')
        {
            $today_date = new DateTime(date("Y-m-d"));
            $checkin_date = new DateTime($chk_avail['checkin']); 
            $checkout_date = new DateTime($chk_avail['checkout']); 
            
            if($checkin_date == $checkout_date)
            {
                echo"<h2 class='bi bi-exclamation-diamond-fill text-center text-danger'>Invalid Dates Entered</h2>";
                exit;
            }
            else if($checkout_date < $checkin_date)
            {
                echo"<h2 class='bi bi-exclamation-diamond-fill text-center text-danger'>Invalid Dates Entered</h2>";
                exit;
            }
            else if($checkin_date < $today_date)
            {
                echo"<h2 class='bi bi-exclamation-diamond-fill text-center text-danger'>Invalid Dates Entered</h2>";
                exit;
            }
        }

        // guests data decode
        $guests = json_decode($_GET['guests'],true);
        $persons = ($guests['persons']!='') ? $guests['persons'] : 0;

        // facilities data decode
        $facility_list = json_decode($_GET['facility_list'],true);

        // features data decode
        $feature_list = json_decode($_GET['feature_list'],true);

        // count the number of rooms and out variable to store room cards
        $count_rooms = 0;
        $output = "";


        // fetching the settings table to check kung yung website is shutdown or not
        $settings_query = "SELECT * FROM `settings` WHERE `sr_no`=1";
        $settings_r = mysqli_fetch_assoc(mysqli_query($con, $settings_query));
    
        // ito yung query para sa room cards with guest filter
        $room_res = select("SELECT * FROM `rooms` WHERE `guest`>=? AND `status`=? AND `removed`=?", [$persons,1, 0], 'iii');

        while ($room_data = mysqli_fetch_assoc($room_res)) {
            
            //check availability filter
            if($chk_avail['checkin']!='' && $chk_avail['checkout']!='')
            {
                $tb_query = "SELECT COUNT(*) AS `total_bookings` FROM `booking_order`
                    WHERE booking_status=? AND room_id=?
                    AND check_out > ? AND check_in < ?";
            
                $values = ['reserved', $room_data['id'], $chk_avail['checkin'], $chk_avail['checkout']];
                $tb_fetch = mysqli_fetch_assoc(select($tb_query, $values, 'siss'));

                if(($room_data['quantity']-$tb_fetch['total_bookings'])==0)
                {
                    continue;
                }
            }

            //get facilities of room with filters
            $facilities_count = 0;

            $facilities_query = mysqli_query($con, "SELECT f.name, f.id FROM `facilities` f 
                INNER JOIN `room_facilities` rfac ON f.id = rfac.facilities_id 
                WHERE rfac.room_id = '$room_data[id]'");

            $facilities_data = "";
            while ($facilities_row = mysqli_fetch_assoc($facilities_query)) 
            {
                if(in_array($facilities_row['id'],$facility_list['facility'])){
                    $facilities_count++;
                }
                $facilities_data .= "<span class='badge rounded-pill bg-light text-dark text-wrap'>
                    $facilities_row[name]
                </span>";
            }

            if(count($facility_list['facility'])!=$facilities_count)
            {
                continue;
            }


            //get features of room with filters
            $features_count = 0;

            $feature_query = mysqli_query($con, "SELECT f.name, f.id FROM `features` f 
                INNER JOIN `room_features` rfea ON f.id = rfea.features_id 
                WHERE rfea.room_id = '$room_data[id]'");

            $features_data = "";
            while ($features_row = mysqli_fetch_assoc($feature_query)) {
                
                if(in_array($features_row['id'],$feature_list['feature'])){
                    $features_count++;
                }

                $features_data .= "<span class='badge rounded-pill bg-light text-dark text-wrap'>
                    $features_row[name]
                </span>";
            }

            if(count($feature_list['feature'])!=$features_count)
            {
                continue;
            }

            //get thumbnail of image

            $room_thumb = ROOMS_IMG_PATH."thumbnail.jpg";
            $thumb_query = mysqli_query($con, "SELECT * FROM `room_images` 
            WHERE `room_id`='$room_data[id]' 
            AND `thumb`='1'");

            if (mysqli_num_rows($thumb_query) > 0) {
                $thumb_res = mysqli_fetch_assoc($thumb_query);
                $room_thumb = ROOMS_IMG_PATH.$thumb_res['image'];
            }

            $book_btn = "";
            // $input = "";


            if(!$settings_r['shutdown'])
            {
                $login = 0;
                if (isset($_SESSION['login']) && $_SESSION['login'] == true) {
                    $login = 1;
                }
                // $input = "<input type='hidden' name='check_availability'>";
                $book_btn = "<button onclick='checkLoginToBook($login,$room_data[id])' class='btn btn-sm w-100 text-white custom-bg shadow-none mb-2'>Book Now</button>";               
            }
            
            // print room card

            $output.="
                <div class='card mb-4 border-0 shadow'>
                    <div class='row g-0 p-3 align-items-center'>
                        <div class='col-md-5 mb-lg-0 mb-md-0 mb-3'>
                            <img src='$room_thumb' class='img-fluid rounded'>
                        </div>
                        <div class='col-md-5 px-lg-3 px-md-3 px-0'>
                            <h5 class='mb-3'>$room_data[name]</h5>
                            <div class='features mb-3'>
                                <h6 class='mb-1'>Amenities</h6>
                                $features_data
                            </div>
                            <div class='facilities mb-3'>
                                <h6 class='mb-1'>Features</h6>
                                $facilities_data
                            </div>
                            <div class='guests mb-3'>
                                <h6 class='mb-1'>Persons</h6>
                                <span class='badge rounded-pill bg-light text-dark text-wrap'>
                                Maximum of: $room_data[guest] Guest/s
                                </span>
                            </div>
                        </div>
                        <div class='col-md-2 mt-lg-0 mt-md-0 mt-4 text-center'>
                            <h6 class='mb-4'>₱$room_data[price] Per hour</h6>
                            $book_btn
                            <a href='room_details.php?id=$room_data[id]' class='btn btn-sm w-100 btn-outline-dark shadow-none'>More details</a>
                        </div>
                    </div>
                </div>
            ";

            $count_rooms++;
        }

        if($count_rooms>0)
        {
            echo $output;
        }
        else
        {
            echo"<h3 class='text-center text-danger'>No rooms to show</h3>";
        }
    }
                
?>