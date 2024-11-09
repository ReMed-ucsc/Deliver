<?php
    require('dp.php');


    if(!empty($_POST['email']) && !empty($_POST['apiKey'])){
        $email = $_POST['email'];
        $apiKey = $_POST['apiKey'];
        $result = array();

        if($conn){
            $sql = "SELECT * FROM users WHERE email = '".$email."' AND apiKey = '".$apiKey."' ";

            $res = mysqli_query($conn, $sql);

            if(mysqli_num_rows($res) != 0){
                $row = mysqli_fetch_assoc($res);
                $result = array("status" => "success", 
                        "message" => "data fetcjed succesful", 
                        "name" => $row['name'], 
                        "email" => $row['email'],
                        "apiKey" => $row['apiKey']);
            }else{
                $result = array("status" => "failed");
            }
        }else{
            $result = array("status" => "failed", "message" => "db failed");
        }
    }else{
        $result = array("status" => "failed", "message" => "all fileds are required");
    }

echo json_encode($result, JSON_PRETTY_PRINT);