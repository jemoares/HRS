<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require('inc/links.php'); ?>
    <title><?php echo $settings_r['site_title'] ?> - ROOM DETAILS</title>
</head>

<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <?php
    if (!isset($_GET['id'])) {
        redirect('rooms.php');
    }

    $data = filteration($_GET);

    $room_res = select("SELECT * FROM `rooms` WHERE `id`=? AND `status`=? AND `removed`=?", [$data['id'], 1, 0], 'iii');

    if (mysqli_num_rows($room_res) == 0) {
        redirect('rooms.php');
    }

    $room_data = mysqli_fetch_assoc($room_res);

    ?>



    <div class="container">
        <div class="row">
            <div class="col-12 my-5 px-4">
                <h2 class="fw-bold"><?php echo $room_data['name'] ?></h2>
                <div style="font-size: 14px;">
                    <a href="index.php" class="text-secondary text-decoration-none">HOME</a>
                    <span class="text-secondary"> > </span>
                    <a href="rooms.php" class="text-secondary text-decoration-none">ROOMS</a>
                </div>
            </div>

            <div class="col-lg-7 col-md-12 px-4">
                <div id="roomCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php
                        $room_img = ROOMS_IMG_PATH . "thumbnail.jpg";
                        $img_query = mysqli_query($con, "SELECT * FROM `room_images` 
                            WHERE `room_id`='$room_data[id]'");

                        if (mysqli_num_rows($img_query) > 0) {
                            $active_class = 'active';

                            while ($img_res = mysqli_fetch_assoc($img_query)) {
                                echo "
                                <div class='carousel-item $active_class'>
                                <img src='" . ROOMS_IMG_PATH . $img_res['image'] . "' class='d-block w-100 rounded'>
                                </div>
                                ";
                                $active_class = '';
                            }
                        } else {
                            echo "<div class='carousel-item active'>
                                <img src='$room_img' class='d-block w-100'>
                                </div>";
                        }
                        ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#roomCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#roomCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>

            <div class="col-lg-5 col-md-12 px-4">
                <div class="card mb-4 border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <?php
                        echo <<<price
                            <h4>₱$room_data[price] Per night</h4>
                        price;

                        echo <<<rating
                            <div class="mb-3">
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                            </div>
                        rating;

                        $feature_query = mysqli_query($con, "SELECT f.name FROM `features` f 
                                INNER JOIN `room_features` rfea ON f.id = rfea.features_id 
                                WHERE rfea.room_id = '$room_data[id]'");

                        $features_data = "";
                        while ($feature_row = mysqli_fetch_assoc($feature_query)) {
                            $features_data .= "<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                                    $feature_row[name]
                                </span>";
                        }

                        echo <<<features
                            <div class="mb-3">
                                <h6 class="mb-1">Amenities</h6>
                                $features_data
                            </div>
                        features;

                        $facilities_query = mysqli_query($con, "SELECT f.name FROM `facilities` f 
                        INNER JOIN `room_facilities` rfac ON f.id = rfac.facilities_id 
                        WHERE rfac.room_id = '$room_data[id]'");

                        $facilities_data = "";
                        while ($facilities_row = mysqli_fetch_assoc($facilities_query)) {
                            $facilities_data .= "<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                            $facilities_row[name]
                            </span>";
                        }

                        echo <<<facilities
                            <div class="mb-3">
                                <h6 class="mb-1">Features</h6>
                                $facilities_data
                            </div>
                        facilities;

                        echo <<<guests
                            <div class="mb-3">
                                <h6 class="mb-1">Persons</h6>
                                    <span class="badge rounded-pill bg-light text-dark text-wrap">
                                        Maximum of: $room_data[guest] Guest/s
                                    </span>
                            </div>
                        guests;

                        echo <<<area
                            <div class="mb-3">
                                <h6 class="mb-1">Area</h6>
                                <span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                                    $room_data[area] sq.ft.
                                </span> 
                            </div>
                        area;

                        if(!$settings_r['shutdown'])
                        {
                            $login = 0;
                            if (isset($_SESSION['login']) && $_SESSION['login'] == true) {
                                $login = 1;
                            }
                            echo <<< book
                                <button onclick='checkLoginToBook($login,$room_data[id])' class="btn w-100 text-white custom-bg shadow-none mb-1">Book Now</button>
                            book;
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-12 mt-4 px-4">
                <div class="mb-5">
                    <h5>Description</h5>
                    <p>
                        <?php
                        echo $room_data['description'];
                        ?>
                    </p>
                </div>
                <div>
                    <h5 class="mb-3">Reviews & Ratings</h5>
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <img src="images/features/star.svg" width="30px">
                            <h6 class="m-0 ms-2">Random Customer</h6>
                        </div>
                        <p>
                            Lorem ipsum, dolor sit amet consectetur adipisicing elit.
                            Doloremque quae quaerat aperiam fugiat cum harum numquam eius ipsa, illo exercitationem nostrum. Hic laborum aliquid similique nisi molestiae omnis, iste suscipit!
                        </p>
                        <div class="rating">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>
</body>

</html>