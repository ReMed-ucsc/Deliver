<?php

include_once '../models/User.php';

class DeliveryMethods{
    private $conn;
    private $driver;
    private $fcmUrl;
    private $serverKey;
    private $table = 'driverlocation';

    public function __construct($conn, $fcmUrl, $serverKey)
    {
        $this->conn = $conn;
        $this->driver = new User($this->conn);
    }

    public function sendLocationRequestToAll(){
        $driverList = $this->driver->getDriversToken();

        if(empty($driverList)){
            return[
                "status" => "error",
                "message" => "No driver found"
            ];
        }

        $payload = [
            "registration_ids" => array_column($driverList, 'fcmToken'),
            "notification" => [
                "title" => "Location Request",
                "body" => "Send Location",
                "sound" => "default"
            ],
            "data" => [
                "message" => "send_location",
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->fcmUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: key=' .$this->serverKey,
            'Content-Type: application/json'
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);

        if($response === false){
            $error = curl_error($ch);
            curl_close($ch);

            return[
                'status' => 'error',
                'message' => 'cUrl error: ' .$error
            ];
        }

        curl_close($ch);

        $responseDecoded = json_decode($response, true);

        if($responseDecoded === null){
            return [
                'status' => 'error',
                'message' => 'failed to decode'
            ];
        }

        return $responseDecoded;
    }
    
    public function addDrivertoTable($data){
        if(!empty($data['driverId']) && !empty($data['fcmToken']) && !empty($data['longitude']) && !empty($data['latitude']) && !empty($data['time'])){
            $query = "INSERT INTO " . $this->table . " SET driverId = :driverId, fcmToken = :fcmToken, longitude = :longitude, latitude = :latitude, time = :time";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':driverId', $data['driverId']);
            $stmt->bindParam(':fcmToken', $data['fcmToken']);
            $stmt->bindParam(':longitude', $data['longitude']);
            $stmt->bindParam(':latitude', $data['latitude']);
            $stmt->bindParam(':time', $data['time']);

            if($stmt->execute()){
                return[
                    "status"  => "success"
                ];
            }
            return [
                "status" => "error"
            ];
        }
    }
}