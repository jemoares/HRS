<?php
require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

if (isset($_POST['add_room'])) {
    $features = filteration(json_decode($_POST['features']));
    $facilities = filteration(json_decode($_POST['facilities']));

    $frm_data = filteration($_POST);
    $flag = 0;

    $query = "INSERT INTO `rooms` (`name`, `area`, `price`, `quantity`, `guest`, `description`) VALUES (?,?,?,?,?,?)";
    $values = [$frm_data['name'], $frm_data['area'], $frm_data['price'], $frm_data['quantity'], $frm_data['guest'], $frm_data['desc']];

    if (insert($query, $values, 'siiiis')) {
        $flag = 1;
    }

    $room_id = mysqli_insert_id($con);

    $query2 = "INSERT INTO `room_facilities`(`room_id`, `facilities_id`) VALUES (?,?)";

    if ($stmt = mysqli_prepare($con, $query2)) {
        foreach ($facilities as $f) {
            mysqli_stmt_bind_param($stmt, 'ii', $room_id, $f);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $flag = 0;
        die('query cannot be perform - insert');
    }

    $query3 = "INSERT INTO `room_features`(`room_id`, `features_id`) VALUES (?,?)";

    if ($stmt = mysqli_prepare($con, $query3)) {
        foreach ($features as $f) {
            mysqli_stmt_bind_param($stmt, 'ii', $room_id, $f);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $flag = 0;
        die('query cannot be perform - insert');
    }

    if ($flag) {
        echo 1;
    } else {
        echo 0;
    }
}

if (isset($_POST['get_all_rooms'])) {
    $res = selectAll('rooms');
    $i = 1;

    $data = "";

    while ($row = mysqli_fetch_assoc($res)) {

        if ($row['status'] == 1) {
            $status = "<button onclick='toggle_status($row[id],0)' class='btn btn-dark btn-sm shadow-none'>active</button>";
        } else {
            $status = "<button onclick='toggle_status($row[id],1)' class='btn btn-danger btn-sm shadow-none'>inactive</button>";
        }

        $data .= "
        <tr class='align-middle'>
            <td>$i</td>
            <td>$row[name]</td>
            <td>$row[area] sq. ft.</td>
            <td>
                <span class='badge rounded-pill bg-light text-dark'>
                    Guest: $row[guest]
                </span><br>
            </td>
            <td>₱$row[price]</td>
            <td>$row[quantity]</td>
            <td>$status</td>
            <td>
                <button type='button' onclick='edit_details($row[id])' class='btn btn-primary shadow-none btn-sm' data-bs-toggle='modal' data-bs-target='#edit-room'>
                    <i class='bi bi-pencil-square'></i>
                </button>
                <button type='button' onclick=\"room_images($row[id],'$row[name]')\" class='btn btn-info shadow-none btn-sm' data-bs-toggle='modal' data-bs-target='#room-images'>
                    <i class='bi bi-images'></i>
                </button>
            </td>
        </tr>
        ";
        $i++;
    }
    echo $data;
}

if (isset($_POST['get_room'])) {
    $frm_data = filteration($_POST);

    $res1 = select("SELECT * FROM `rooms` WHERE `id`=?", [$frm_data['get_room']], 'i');
    $res2 = select("SELECT * FROM `room_features` WHERE `room_id`=?", [$frm_data['get_room']], 'i');
    $res3 = select("SELECT * FROM `room_facilities` WHERE `room_id`=?", [$frm_data['get_room']], 'i');

    $roomdata = mysqli_fetch_assoc($res1);
    $features = [];
    $facilities = [];

    if (mysqli_num_rows($res2) > 0) {
        while ($row = mysqli_fetch_assoc($res2)) {
            array_push($features, $row['features_id']);
        }
    }

    if (mysqli_num_rows($res3) > 0) {
        while ($row = mysqli_fetch_assoc($res3)) {
            array_push($facilities, $row['facilities_id']);
        }
    }

    $data = ["roomdata" => $roomdata, "features" => $features, "facilities" => $facilities];

    $data = json_encode($data);

    echo $data;
}

if (isset($_POST['edit_room'])) {
    $features = filteration(json_decode($_POST['features']));
    $facilities = filteration(json_decode($_POST['facilities']));

    $frm_data = filteration($_POST);
    $flag = 0;

    $query = "UPDATE `rooms` SET `name`=?, `area`=?, `price`=?,
    `quantity`=?, `guest`=?, `description`=? WHERE `id`=?";

    $values = [$frm_data['name'], $frm_data['area'], $frm_data['price'], $frm_data['quantity'], $frm_data['guest'], $frm_data['desc'], $frm_data['room_id']];

    if (update($query, $values, 'siiiisi')) {
        $flag = 1;
    }
    $del_features = delete("DELETE FROM `room_features` WHERE `room_id`=?", [$frm_data['room_id']], 'i');
    $del_facilities = delete("DELETE FROM `room_facilities` WHERE `room_id`=?", [$frm_data['room_id']], 'i');

    if (!($del_facilities && $del_features)) {
        $flag = 0;
    }

    $query2 = "INSERT INTO `room_facilities`(`room_id`, `facilities_id`) VALUES (?,?)";

    if ($stmt = mysqli_prepare($con, $query2)) {
        foreach ($facilities as $f) {
            mysqli_stmt_bind_param($stmt, 'ii',$frm_data['room_id'], $f);
            mysqli_stmt_execute($stmt);
        }
        $flag = 1;
        mysqli_stmt_close($stmt);
    } else {
        $flag = 0;
        die('query cannot be perform - insert');
    }

    $query3 = "INSERT INTO `room_features`(`room_id`, `features_id`) VALUES (?,?)";

    if ($stmt = mysqli_prepare($con, $query3)) {
        foreach ($features as $f) {
            mysqli_stmt_bind_param($stmt, 'ii',$frm_data['room_id'], $f);
            mysqli_stmt_execute($stmt);
        }
        $flag = 1;
        mysqli_stmt_close($stmt);
    } else {
        $flag = 0;
        die('query cannot be perform - insert');
    }

    if ($flag) {
        echo 1;
    } else {
        echo 0;
    }
}

if (isset($_POST['toggle_status'])) {
    $frm_data = filteration($_POST);

    $query = "UPDATE `rooms` SET `status`=? WHERE `id`=?";
    $values = [$frm_data['value'], $frm_data['toggle_status']];

    if (update($query, $values, 'ii')) {
        echo 1;
    } else {
        echo 0;
    }
}

if(isset($_POST['add_image']))
    {
        $frm_data = filteration($_POST);

        $img_r = uploadImage($_FILES['image'],ROOMS_FOLDER);
        
        if($img_r == 'inv_img')
        {
            echo $img_r;;
        }
        else if($img_r == 'inv_size')
        {
            echo $img_r;
        }
        else if($img_r == 'upd_failed')
        {
            echo $img_r;
        }
        else
        {
            $query = "INSERT INTO `room_images`(`room_id`, `image`) VALUES (?,?)";
            $values = [$frm_data['room_id'],$img_r];
            $res = insert($query, $values, 'is');
            echo $res;
        }
    }
