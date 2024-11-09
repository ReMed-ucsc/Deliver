<?php
include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Delivery.php';

class DeliveryController{
    private $db;
    private $delivery;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
        $this->delivery = new Delivery($this->db);
    }

    public function acceptDelivery($data){
        if(!empty($data['orderId']) && !empty($data['driverId']) && !empty($data['address']) && !empty($data['delLongitude']) && !empty($data['delLatitude']) && !empty($data['date'])){
            $this->delivery->orderId = $data['orderId'];
            $this->delivery->driverId = $data['driverId'];
            $this->delivery->address = $data['address'];
            $this->delivery->delLongitude = $data['delLongitude'];
            $this->delivery->delLatitude = $data['delLatitude'];
            $this->delivery->date = $data['date'];

            if($this->delivery->addDelivery()){
                return[
                    "message" => "delivery added successfully"
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

    public function breakDown($data){
        $orderId = $this->delivery::reportBreakdown($this->delivery, $data['deliveryId']);

        if($orderId){

        }
    }
}