<?php

require('admin/inc/db_config.php');
require('admin/inc/essentials.php');


if(isset($_GET['email']) && isset($_GET['token']))
{
    $query = "SELECT * FROM `user_cred` WHERE `email` = '$_GET[email]' AND `token`='$_GET[token]'";
    $result = mysqli_query($con, $query);
    if($result)
    {
        if(mysqli_num_rows($result)==1)
        {
            $result_fetch = mysqli_fetch_assoc($result);
            if($result_fetch['is_verified']==0)
            {
                $update = "UPDATE `user_cred` SET `is_verified` = '1' WHERE `email` = '$result_fetch[email]'";
                if(mysqli_query($con, $update))
                {
                    echo"
                        <script>
                            alert('Email verification Successful');
                            window.location.href='index.php';
                        </script>
                    ";
                }
                else
                {
                    echo"
                        <script>
                            alert('Cannot run query');
                            window.location.href='index.php';
                        </script>     
                    ";
                }
            }
            else
            {
                echo"
                    <script>
                        alert('Email already registered');
                        window.location.href='index.php';
                    </script>
                ";
            }
        }
    
    }
    else
    {
        echo"
        <script>
            alert('Cannot run query');
            window.location.href='index.php';
        </script>
        
        ";
    }
}








// if(isset($_GET['email_confirmation']) && isset($_GET['token']))
// {
//     $data = filteration($_GET);

//     $query = select("SELECT * FROM `user_cred` WHERE `email`=? AND `token`=? LIMIT 1",
//     [$data['email'],$data['token']],'ss');

//     if(mysqli_num_rows($query)==1)
//     {
//         $fetch = mysqli_fetch_assoc($query);

//         if($fetch['is_verified']==1){
//             echo"<script>alert('Email already verified')</script>";   
//         }
//         else{
//             $update = update("UPDATE `user_cred` SET `is_verified`=? WHERE `id`=?",[1,$fetch['id']],'ii');
//             if($update){
//                 echo"<script>alert('Email verification successful')</script>";
//             }
//             else{
//                 echo"<script>alert('Email verification failed, Server Down')</script>";
//             }
//             redirect('index.php');
//         }
//     }
//     else{
//         echo"<script>alert('Invalid Link')</script>";
//     }
// }

?>