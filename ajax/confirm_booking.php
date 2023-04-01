<?php

    require('../admin/inc/db_config.php');
    require('../admin/inc/essentials.php');
    date_default_timezone_set("Asia/Manila");

    if(isset($_POST['check_availability']))
    {
        $frm_data = filteration($_POST);
        $status = "";
        $result = "";
        
        //CHECK IN TSAKA OUT VALIDATIONS

        
        $today_date = new DateTime(date("Y-m-d"));
        $checkin_date = new DateTime($frm_data['check_in']); 
        $checkout_date = new DateTime($frm_data['check_out']); 
        
        if($checkin_date == $checkout_date)
        {
            $status = 'check_in_out_equal';
            $result = json_encode(["status"=>$status]);
        }
        else if($checkout_date < $checkin_date)
        {
            $status = 'check_out_earlier';
            $result = json_encode(["status"=>$status]);
        }
        else if($checkin_date < $today_date)
        {
            $status = 'check_in_earlier';
            $result = json_encode(["status"=>$status]);
        }

        //CHECK BOOKING AVAILABILITY KAPAG YUNG STATUS IS BLANK MAG RERETURN SIYA AS ERROR
        
        if($status != '')
        {
            echo $result;
        }
        else
        {
            session_start();
            $_SESSION['room'];
            
            //TO CHECK KUNG YUNG ROOM IS AVAILABLE OR HINDI
            $count_days = date_diff($checkin_date,$checkout_date)->days;
            $payment = $_SESSION['room']['price'] * $count_days;

            $_SESSION['room']['payment'] = $payment;
            $_SESSION['room']['available'] = true;
            
            $result = json_encode(["status"=>'available', "days"=>$count_days, "payment"=> $payment]);
            echo $result;
        }
    }
?>