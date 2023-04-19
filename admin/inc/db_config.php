<?php
    $hname = 'localhost';
    $uname = 'root';
    $pass = '';
    $db = 'hotelwe';

    $con = mysqli_connect($hname,$uname,$pass,$db);

    if(!$con){
        die("Cannot connect to database". mysqli_connect_error());    
    }


    function filteration($data){
        foreach($data as $key => $value){
            $value = trim($value);
            $value = stripslashes($value);
            $value = htmlspecialchars($value);
            $value = strip_tags($value);

            $data[$key] = $value;
        }
        return $data;
    }

    function selectAll($table)
    {
        $con = $GLOBALS['con'];
        $res = mysqli_query($con, "SELECT * FROM $table");
        return $res;
    }

    function select($sql, $values, $datatypes)
    {
        $con = $GLOBALS['con'];
        if($stmt = mysqli_prepare($con, $sql))
        {
            mysqli_stmt_bind_param($stmt, $datatypes,...$values);
            if(mysqli_stmt_execute($stmt)){
               $res = mysqli_stmt_get_result($stmt);
               mysqli_stmt_close($stmt);
               return $res;     
            }
            else{
                mysqli_stmt_close($stmt);
                die("Query cannot be perform - Select");
            }           
        }
        else{
            die("Query cannot be prepared - Select");
        }
    }
    
    function update($sql, $values, $datatypes)
    {
        $con = $GLOBALS['con'];
        if($stmt = mysqli_prepare($con, $sql))
        {
            mysqli_stmt_bind_param($stmt, $datatypes,...$values);
            if(mysqli_stmt_execute($stmt)){
               $res = mysqli_stmt_affected_rows($stmt);
               mysqli_stmt_close($stmt);
               return $res;     
            }
            else{
                mysqli_stmt_close($stmt);
                die("Query cannot be perform - Update");
            }           
        }
        else{
            die("Query cannot be prepared - Update");
        }
    }

    function insert($sql, $values, $datatypes)
    {
        $con = $GLOBALS['con'];
        if($stmt = mysqli_prepare($con, $sql))
        {
            mysqli_stmt_bind_param($stmt, $datatypes,...$values);
            if(mysqli_stmt_execute($stmt)){
               $res = mysqli_stmt_affected_rows($stmt);
               mysqli_stmt_close($stmt);
               return $res;     
            }
            else{
                mysqli_stmt_close($stmt);
                die("Query cannot be perform - Insert");
            }           
        }
        else{
            die("Query cannot be prepared - Insert");
        }
    }

    function delete($sql, $values, $datatypes)
    {
        $con = $GLOBALS['con'];
        if($stmt = mysqli_prepare($con, $sql))
        {
            mysqli_stmt_bind_param($stmt, $datatypes,...$values);
            if(mysqli_stmt_execute($stmt)){
               $res = mysqli_stmt_affected_rows($stmt);
               mysqli_stmt_close($stmt);
               return $res;     
            }
            else{
                mysqli_stmt_close($stmt);
                die("Query cannot be perform - Delete");
            }           
        }
        else{
            die("Query cannot be prepared - Delete");
        }
    }
?>