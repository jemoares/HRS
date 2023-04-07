<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require('inc/links.php'); ?>
    <title><?php echo $settings_r['site_title'] ?> - ROOMS</title>
</head>

<body class="bg-light">

    <?php 
    
        require('inc/header.php');

        $checkin_default="";
        $checkout_default="";
        $guest_default="";

        if(isset($_GET['check_availability']))
        {
            $frm_data = filteration($_GET);

            $checkin_default=$frm_data['checkin'];
            $checkout_default=$frm_data['checkout'];
            $guest_default=$frm_data['guest'];
        }
    ?>

    <div class="my-5 px-4">
        <h2 class="fw-bold h-font text-center">ROOMS</h2>
        <div class="h-line bg-dark"></div>
    </div>

    <div class="container-fluid">
        <div class="row">

            <div class="col-lg-3 col-md-12 mb-lg-0 mb-4 ps-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow">
                    <div class="container-fluid flex-lg-column align-items-stretch">
                        <h4 class="mt-2">FILTERS</h4>
                        <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterDropdown" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse flex-column align-items-stretch mt-2" id="filterDropdown">
                            <!-- Check Avaiability -->
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="d-flex align-items-center justify-content-between mb-3" style="font: size 18px;">
                                <span>CHECK AVAILABILITY</span>
                                <button id="chk_avail_btn" onclick="chk_avail_clear()" class="btn shadow-none btn-sm text-secondary d-none">Reset</button>
                                </h5>
                                <label class="form-label">Check-in</label>
                                <input type="date" class="form-control shadow-none mb-3"  min="<?php echo date("Y-m-d"); ?>" value="<?php echo $checkin_default ?>" id="checkin" onchange="chk_avail_filter()">
                                <label class="form-label">Check-out</label>
                                <input type="date" class="form-control shadow-none"  min="<?php echo date("Y-m-d"); ?>" value="<?php echo $checkout_default ?>"id="checkout" onchange="chk_avail_filter()">
                            </div>
                            <!-- Check Amenities -->
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="d-flex align-items-center justify-content-between mb-3" style="font: size 18px;">
                                    <span>AMENITIES</span>
                                    <button id="features_btn" onclick="features_clear()" class="btn shadow-none btn-sm text-secondary d-none">Reset</button>
                                </h5>
                                <?php 
                                    $features_query = selectAll('features');
                                    while($row = mysqli_fetch_assoc($features_query))
                                    {
                                        echo<<<features
                                            <div class="mb-2">
                                                <input type="checkbox" onclick="fetch_rooms()" name="features" value="$row[id]" class="form-check-input shadow-none me-1" id="$row[id]">
                                                <label class="form-check-label" for="$row[id]">$row[name]</label>
                                            </div>
                                        features;
                                    }                   
                                ?>
                            </div>

                            <!-- Check Features -->
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="d-flex align-items-center justify-content-between mb-3" style="font: size 18px;">
                                    <span>FEATURES</span>
                                    <button id="facilities_btn" onclick="facilities_clear()" class="btn shadow-none btn-sm text-secondary d-none">Reset</button>
                                </h5>
                                <?php 
                                    $facilities_query = selectAll('facilities');
                                    while($row = mysqli_fetch_assoc($facilities_query))
                                    {
                                        echo<<<facilities
                                            <div class="mb-2">
                                                <input type="checkbox" onclick="fetch_rooms()" name="facilities" value="$row[id]" class="form-check-input shadow-none me-1" id="$row[id]">
                                                <label class="form-check-label" for="$row[id]">$row[name]</label>
                                            </div>
                                        facilities;
                                    }                 
                                ?>
                            </div>

                            <!-- Check Room Category -->
                            <!-- <div class="col-lg-3 mb-3">
                            <label class="form-label" style="font-weight: 500;">Room type</label>
                            <select class="form-select shadow-none" name="room_type">
                                
                                    $room_type_q = mysqli_fetch_assoc(mysqli_query($con, "SELECT `name` FROM `rooms`"));
                                
                                

                            </select>
                            </div> -->
                            <!-- Guests -->
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="d-flex align-items-center justify-content-between mb-3" style="font: size 18px;">
                                    <span>GUESTS</span>
                                    <button id="guests_btn" onclick="guests_clear()" class="btn shadow-none btn-sm text-secondary d-none">Reset</button>
                                </h5>
                                <div class="d-flex">
                                    <div class="me-3">
                                        <label class="form-label">Person/s</label>
                                        <input type="number" min="1" id="persons" value="<?php echo $guest_default ?>" oninput="guests_filter()" class="form-control shadow-none ">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>

            <div class="col-lg-9 col-md-12 px-4" id="rooms-data">

            </div>
        </div>
    </div>

<script>

    
    let rooms_data = document.getElementById('rooms-data');
    let checkin = document.getElementById('checkin');
    let checkout = document.getElementById('checkout');
    let chk_avail_btn = document.getElementById('chk_avail_btn');

    let persons = document.getElementById('persons');
    let guests_btn = document.getElementById('guests_btn');

    let facilities_btn = document.getElementById('facilities_btn');
    let features_btn = document.getElementById('features_btn');


    function fetch_rooms()
    {
        let chk_avail = JSON.stringify({
            checkin: checkin.value,
            checkout: checkout.value
        });

        let guests = JSON.stringify({

            persons: persons.value

        });
        let feature_list = {"feature":[]};

        let get_features = document.querySelectorAll('[name="features"]:checked');
        if (get_features.length > 0)
        {
            get_features.forEach((feature)=>{
                feature_list['feature'].push(feature.value);
                // facility_list.facilities.push(facility.value);
            });
            features_btn.classList.remove('d-none');
        }
        else
        {
            features_btn.classList.add('d-none');
        }

        feature_list = JSON.stringify(feature_list);
        
        let facility_list = {"facility":[]};

        let get_facilities = document.querySelectorAll('[name="facilities"]:checked');
        if (get_facilities.length > 0)
        {
            get_facilities.forEach((facility)=>{
                facility_list['facility'].push(facility.value);
                // facility_list.facilities.push(facility.value);
            });
            facilities_btn.classList.remove('d-none');
        }
        else
        {
            facilities_btn.classList.add('d-none');
        }

        facility_list = JSON.stringify(facility_list);

        let xhr = new XMLHttpRequest();
        xhr.open("GET", "ajax/rooms.php?fetch_rooms&chk_avail="+chk_avail+"&guests="+guests+"&facility_list="+facility_list+"&feature_list="+feature_list, true);

        xhr.onprogress = function(){
            rooms_data.innerHTML = `<div class="spinner-border text-info mb-3 d-block mx-auto" id="loader" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>`;
        }
        
        xhr.onload = function(){
            rooms_data.innerHTML = this.responseText;

        }
        
        xhr.send();

    }

    function chk_avail_filter()
    {
        if(checkin.value!='' && checkout.value!='')
        {
            fetch_rooms();
            chk_avail_btn.classList.remove('d-none');
            
        }
    }
    
    function chk_avail_clear()
    {
        checkin.value='';
        checkout.value='';
        chk_avail_btn.classList.add('d-none');
        fetch_rooms();

    }

    function guests_filter()
    {
        if(persons.value>0)
        {
            fetch_rooms();
            guests_btn.classList.remove('d-none');
        }
    }

    function guests_clear()
    {
        persons.value='';
        guests_btn.classList.add('d-none');
        fetch_rooms();
    }

    function features_clear()
    {
        let get_features = document.querySelectorAll('[name="features"]:checked');
        get_features.forEach((feature)=>{
                // facility_list['facility'].push(facility.value);
            feature.checked=false;
        });
        features_btn.classList.add('d-none');
        fetch_rooms();
    }

    function facilities_clear()
    {
        let get_facilities = document.querySelectorAll('[name="facilities"]:checked');
        get_facilities.forEach((facility)=>{
                // facility_list['facility'].push(facility.value);
            facility.checked=false;
        });
        facilities_btn.classList.add('d-none');
        fetch_rooms();
    }


    window.onload=function(){
        fetch_rooms();
    } 
</script>

<?php require('inc/footer.php'); ?>
</body>
</html>

