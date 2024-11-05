<?php
    
    if(!empty($_POST['email']) && !empty($_POST['password'])){
        $email = $_POST['email'];
        $password = $_POST['password'];

        $result = array();

        $con = mysqli_connect("192.168.1.20:3306", "admin", "root", "test");

        if($conn){
            $sql = "SELECT * FROM users WHERE email = '".$email."'";
            $res = mysqli_query($conn, $sql);

            if(mysqli_num_rows($res) != 0){
                $row = mysqli_fetch_assoc($res);

                if($email == $row['email'] && password_verify($password, $row['password'])){
                    try{
                        $aprKey = bin2hex(random_bytes(23));
                    }catch(Exception $e){
                        $apiKey = bin2hex(uniqid($email, true));
                    }

                    $sqlUpdate = "UPDATE users SET apiKey = '".$apiKey."' WHERE email =  '".$email."' ";

                    if(mysqli_query($con, $sqlUpdate)){
                        $result = array("status" => "success", "message" => "login successful", 
                        "name" => $row['name'], 
                        "email" => $row['email'],
                        "apiKey" => $row['apiKey']);
                    }else{
                        $result = array("status" => "failes");
                    }
                }else{
                    $result = array("status" => "failed");
                }
            }else{
                $result = array("status" => "failed");
            }
        }else{
            $result = array("status" => "failed");
        }
    }else{
        $result = array("status" => "failed");
    }

    echo json_encode($result, JSON_PRETTY_PRINT);