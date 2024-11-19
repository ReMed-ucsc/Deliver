<?php
include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Delivery.php';
include_once __DIR__ . '/../utils/driverSearch.php';

class DeliveryController{
    private $db;
    private $delivery;
    private $deliveryMethods;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
        $this->delivery = new Delivery($this->db);
        $this->deliveryMethods = new DeliveryMethods($this->db);

    }

    public function acceptDelivery($data){
        if(!empty($data['orderId']) && !empty($data['apiKey'])){
            $orderDetails = $this->delivery->getOrderDetails($data['orderId']);

            if(!$orderDetails){
                return[
                    "message" => "order not found"
                ];
            }

            $this->delivery->orderId = $data['orderId'];
            $this->delivery->addressPharmacy = $orderDetails['addressPharmacy'];
            $this->delivery->addressDelivery = $orderDetails['addressDelivery'];
            //$this->delivery->longitude = $orderDetails['delLongitude'];
            //$this->delivery->latitude = $orderDetails['delLatitude'];
            $this->delivery->date = $orderDetails['date'];

            if($this->delivery->addDelivery($data['apiKey'])){
                return[
                    "status" => "success",
                    "data"=> [
                        "orderId" => $data['orderId'],
                        "pharmacyName" => $orderDetails['pharmacyName'],
                        "pharmacyAddress" => $orderDetails['addressPharmacy'],
                        "deliveryAddress" => $orderDetails['addressDelivery'],
                        "contactNumber" => $orderDetails['contactNo']
                    ]
                ];
            }else{
                return[
                    "message" => "failed to add delivery record"
                ];
            }
        }
    }

    public function completeDelivery($data){
        if(!empty($data['deliveryId']) && !empty($data['deliveredTime'])){
            $this->delivery->deliveryId = $data['deliveryId'];
            $this->delivery->deliverdTime = $data['time'];

            return $this->delivery->completeDelivery();
        }
    }

    //send fcm to the driver
    public function notifyDrivers($data){
        if(!empty($data)){
            $this->delivery->addressPharmacy = $data['addressPharmacy'];
            $this->delivery->latitude = $data['latitude'];
            $this->delivery->longitude = $data['longitude'];
            $this->delivery->driverId = $data['driverId'];
            $this->delivery->addressDelivery = $data['addressDelivery'];
            $this->delivery->orderId = $data['orderId'];
            $response = $this->delivery->sendDetailstoDriver();

            return [
                "message" => "success",
                "data" => $response
            ];
        }
    }

    public function breakDown($data){
        $orderId = $this->delivery::reportBreakdown($this->delivery, $data['deliveryId']);

        if($orderId){
            // get all drivers location
            $this->deliveryMethods->sendLocationRequestToAll();

            
            //next function would be waiting for the drivers to respond
            //but its syncronous so the system will wait.
        }
    }
}