<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require('inc/links.php'); ?>
    <title><?php echo $settings_r['site_title'] ?> - BOOKINGS</title>
</head>

<body class="bg-light">

    <?php 
    
        require('inc/header.php'); 

        if (!(isset($_SESSION['login']) && $_SESSION['login'] == true)) {
        redirect('index.php');
        }
    
    ?>
    
    
    <div class="container">
        <div class="row">
            <div class="col-12 my-5 px-4">
                <h2 class="fw-bold">BOOKING</h2>
                <div style="font-size: 14px;">
                    <a href="index.php" class="text-secondary text-decoration-none">HOME</a>
                    <span class="text-secondary"> > </span>
                    <a href="#" class="text-secondary text-decoration-none">BOOKINGS</a>
                </div>
            </div>
            <?php 
                $query = "SELECT bo.*, bd.* FROM `booking_order` bo
                    INNER JOIN `booking_details` bd ON bo.booking_id = bd.booking_id
                    WHERE ((bo.booking_status='reserved') 
                    OR (bo.booking_status='cancelled'))
                    -- OR (bo.booking_status='cancelling'))
                    AND (bo.user_id=?)
                    ORDER BY bo.booking_id DESC";

                $result = select($query,[$_SESSION['uId']],'i');
                while($data = mysqli_fetch_assoc($result))
                {
                    $date = date("m-d-Y", strtotime($data['datentime']));
                    $checkin = date("m-d-Y", strtotime($data['check_in']));
                    $checkout = date("m-d-Y", strtotime($data['check_out']));

                    $status_bg = "";
                    $btn="";

                    if($data['booking_status']=='reserved')
                    {
                        $status_bg = "bg-success";
                        
                        if($data['arrival']==1)
                        {
                            $btn="<a href='generate_pdf.php?gen_pdf&id=$data[booking_id]' class='btn btn-dark btn-sm shadow-none'>Download PDF</a>";
                                
                            
                            if($data['rate_review']==0)
                            {
                                $btn.="<button type='button' onclick='review_room($data[booking_id],$data[room_id])' data-bs-toggle='modal' data-bs-target='#reviewModal' class='btn btn-dark btn-sm shadow-none ms-2'>Rate & Review</button>";
                            }
                        }
                        else
                        {
                            $btn="<button onclick='cancel_reservation($data[booking_id])' type='button' class='btn btn-danger btn-sm shadow-none'>Cancel</button>";
                        }
                    }
                    // else if($data['booking_status']=='cancelled')
                    // {
                    //     $status_bg = "bg-danger";
                    //     $btn="<a href='generate_pdf.php?gen_pdf&id=$data[booking_id]' class='btn btn-dark btn-sm shadow-none'>Download PDF</a>";

                    //     // $btn="<span class='badge bg-primary'>Cancellation in process</span>";   
                    // }
                    else
                    {
                        $status_bg = "bg-danger";
                        $btn="<a href='generate_pdf.php?gen_pdf&id=$data[booking_id]' class='btn btn-dark btn-sm shadow-none'>Download PDF</a>";

                    }                    
                    echo<<<bookings
                        <div class='col-md-4 px-4 mb-4'>
                            <div class='bg-white p-3 rounded shadow-sm'>
                                <h5 class='fw-bold'>$data[room_name]</h5>
                                <p>₱$data[price] Per Night</p>
                                <p>
                                    <b>Check-in: </b> $checkin <br>
                                    <b>Check-out: </b> $checkout
                                </p>
                                <p>
                                    <b>Amount: </b> ₱$data[trans_amt] <br>
                                    <b>Order ID: </b> $data[order_id] <br>
                                    <b>Date: </b> $date
                                </p>
                                <p>
                                    <span class='badge $status_bg'>$data[booking_status]</span>
                                </p>  
                                $btn
                            </div>
                        </div>
                    bookings;
                }    
            ?>

         </div>
    </div>

    <div class="modal fade" id="reviewModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="review-form">
                    <div class="modal-header">
                        <h5 class="modal-title d-flex align-items-center">
                            <i class="bi bi-chat-square-heart-fill fs-3 me-2"></i>Rate & Review
                        </h5>
                        <button type="reset" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                                <select class="form-select shadow-none" name="rating">
                                    <option value="5">Excellent</option>
                                    <option value="4">Great</option>
                                    <option value="3">Very Good</option>
                                    <option value="2">Good</option>
                                    <option value="1">Bad</option>
                                </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Review</label>
                            <textarea type="password" name="review" rows="3" required class="form-control shadow-none"></textarea>
                        </div>

                        <input type="hidden" name="booking_id">
                        <input type="hidden" name="room_id">

                        <div class="text-end">
                            <button type="submit" class="text-white custom-bg btn-sm shadow-none">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <?php
        if(isset($_GET['cancel_status']))
        {
            alert('success', 'Reservation Cancelled');
        }
        else if(isset($_GET['review_status']))
        {
            alert('success', 'Thank you for rating & review');
        }

    ?>

    <?php require('inc/footer.php'); ?>

    <script>

        function cancel_reservation(id)
        {
            if(confirm('Are you sure to cancel reservation?'))
            {
                let xhr = new XMLHttpRequest();
                xhr.open("POST", "ajax/cancel_reservation.php", true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onload = function () {
                    if(this.responseText==1)
                        {
                            window.location.href="bookings.php?cancel_status=true";
                        }
                        else
                        {
                            alert('error','Cancellation failed');
                        }        
                    }

                xhr.send('cancel_reservation&id='+id);
            }
        }

        let review_form = document.getElementById('review-form');

        function review_room(bid,rid)
        {
            review_form.elements['booking_id'].value = bid;
            review_form.elements['room_id'].value = rid;

        }

        review_form.addEventListener('submit', function(e){
            e.preventDefault();

            let data = new FormData();
            data.append('review_form','');
            data.append('rating',review_form.elements['rating'].value);
            data.append('review',review_form.elements['review'].value);
            data.append('booking_id',review_form.elements['booking_id'].value);
            data.append('room_id',review_form.elements['room_id'].value);


            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/review_room.php", true);

            xhr.onload = function () 
            {


                if(this.responseText==1)
                {        
                    window.location.href = 'bookings.php?review_status=true';
                          
                }     
                else
                {
                    var myModal = document.getElementById('reviewModal');
                    var modal = bootstrap.Modal.getInstance(myModal);
                    modal.hide();

                    alert('error', "Rating & Review Failed"); 
                }
            }

            xhr.send(data);
        });
    </script>

</body>

</html>