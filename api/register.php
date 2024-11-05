<?php

if(!empty($_POST['name']) && !empty($_POST['password']) && !empty($_POST['email'])){

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);


    $con = mysqli_connect("192.168.1.20:3306", "admin", "root", "test");
    
    if($con){
        $sql = "INSERT INTO users (name, email, password) VALUES ('".$name."', '".$email."', '".$password."')";

        if(mysqli_query($conn, $sql)){
            echo "success";
        }else{
            echo "false";
        }
    }else 
        echo "database not connected";

}else{
    echo "All filed are required";
}