<?php

include_once '../models/User.php';

class DeliveryMethods {
    private $conn;
    private $driver;
    private $fcmUrl;
    private $serverKey;
    private $table = 'driverlocation';

    public function __construct($conn, $fcmUrl, $serverKey) {
        $this->conn = $conn;
        $this->driver = new User($this->conn);
        $this->fcmUrl = $fcmUrl;
        $this->serverKey = $serverKey;
    }

    public function sendLocationRequestToAll() {
        $driverList = $this->driver->getDriversToken();

        if (empty($driverList)) {
            return [
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

        return $this->fcmSender($payload);
    }

    public function sendDetailstoDriver($driverId, $deliverLatitude, $deliverLongitude, $pharmacyAddress) {
        $query = "SELECT fcmToken FROM useres WHERE driverId = :driverId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':driverId', $driverId);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $payload = [
                "to" => $result['fcmToken'],
                "notification" => [
                    "title" => "New delivery Request",
                    "body" => "You have a new delivery",
                    "sound" => "default"
                ],
                "data" => [
                    "message" => "delivery_request",
                    "delivery_details" => [
                        "deliveryLongitude" => $deliverLongitude,
                        "deliveryLatitude" => $deliverLatitude,
                        "pharmacyAddress" => $pharmacyAddress
                    ]
                ]
            ];

            return $this->fcmSender($payload);
        } else {
            return [
                "status" => "error",
                "message" => "No driver found"
            ];
        }
    }

    public function addDrivertoTable($data) {
        if (!empty($data['driverId']) && !empty($data['fcmToken']) && !empty($data['longitude']) && !empty($data['latitude']) && !empty($data['time'])) {
            $query = "SELECT time FROM " . $this->table . " WHERE driverId = :driverId ORDER BY time DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':driverId', $data['driverId']);
            $stmt->execute();

            $lastRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            // Insert new data if last update is older than 1 minute
            if (!$lastRecord || (strtotime($data['time']) - strtotime($lastRecord['time']) > 60)) {
                $query = "INSERT INTO " . $this->table . " SET driverId = :driverId, fcmToken = :fcmToken, longitude = :longitude, latitude = :latitude, time = :time";
                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(':driverId', $data['driverId']);
                $stmt->bindParam(':fcmToken', $data['fcmToken']);
                $stmt->bindParam(':longitude', $data['longitude']);
                $stmt->bindParam(':latitude', $data['latitude']);
                $stmt->bindParam(':time', $data['time']);

                if ($stmt->execute()) {
                    return [
                        "status" => "success"
                    ];
                }
            }

            return [
                "status" => "error",
                "message" => "Data not added, recent update already exists"
            ];
        }

        return [
            "status" => "error",
            "message" => "Invalid data provided"
        ];
    }

    public function getDistance($data) {
        $query = "SELECT driverId, longitude, latitude FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $distances = [];

            foreach ($drivers as $driver) {
                $distance = $this->calculateDistance(
                    $data['longitude'], 
                    $data['latitude'], 
                    $driver['longitude'], 
                    $driver['latitude']
                );

                $distances[] = [
                    'driverId' => $driver['driverId'],
                    'distance' => $distance
                ];
            }

            usort($distances, function($a, $b) {
                return $a['distance'] <=> $b['distance'];
            });

            return $distances;
        } else {
            return [
                'status' => 'error',
                'message' => 'No drivers found'
            ];
        }
    }

    public function calculateDistance($longitude1, $latitude1, $longitude2, $latitude2) {
        $earthRadius = 6371; // Radius of Earth in kilometers

        // Convert latitude and longitude from degrees to radians
        $lat1 = deg2rad($latitude1);
        $lon1 = deg2rad($longitude1);
        $lat2 = deg2rad($latitude2);
        $lon2 = deg2rad($longitude2);

        // Haversine formula
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) * sin($dlat / 2) +
             cos($lat1) * cos($lat2) *
             sin($dlon / 2) * sin($dlon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function fcmSender($payload) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->fcmUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: key=' . $this->serverKey,
            'Content-Type: application/json'
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'status' => 'error',
                'message' => 'cURL error: ' . $error
            ];
        }

        curl_close($ch);

        $responseDecoded = json_decode($response, true);

        if ($responseDecoded === null) {
            return [
                'status' => 'error',
                'message' => 'Failed to decode response'
            ];
        }

        return $responseDecoded;
    }
}
