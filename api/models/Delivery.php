<?php
class Delivery{
    private $conn;
    private $table = 'delivery';

    public $deliveryId;
    public $driverId;
    public $orderId;
    public $address;
    public $delLongitude;
    public $delLatitude;
    public $date;
    public $deliverdTime;
    public $status;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function addDelivery(){
        $query = "INSERT INTO " .$this->table . " SET driverId = :driverId, orderId = :orderId, address = :address, longitude = :delLongitude, latitude =:delLatitude, date = :date, deliveredTime = :deliverdTime, status = 'accepted'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':driverId', $this->driverId);
        $stmt->bindParam(':orderId', $this->orderId);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':longitude', $this->delLatitude);
        $stmt->bindParam(':latitude', $this->delLongitude);
        $stmt->bindParam('date', $this->date);
        $stmt->bindParam('deliverdTime', $this->deliverdTime);

        if($stmt->execute()){
            $query = "SELECT deliveryId FROM ". $this->table. " WHERE orderId = :orderId AND status = 'accepted'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':orderId', $this->orderId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->deliveryId = $result['deliveryId'];

            return[
                "status" => "success",
                "deliveryId" => $this->deliveryId
            ];
        }else{
            return[
                "status" => "failure"
            ];
        }
    }
    public function completeDelivery(){
        $query = "UPDATE " . $this->table . " SET status = 'complete', deliveredTime = :deliveredTime WHERE deliveryId = :deliveryId";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':deliveryId', $this->deliveryId);
        $stmt->bindParam(':deliveredTime', $this->deliverdTime);

        if($stmt->execute()){
            return[
                "status" => "success"
            ];
        }else{
            return[
                "status" => "failure"
            ];
        }
    }

    public static function reportBreakdown($db, $deliveryId){
        $query = "UPDATE delivery SET status = 'breakdown' WHERE deliveryId = :deliveryId";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':deliveryId', $deliveryId);
        $stmt->execute();

        //search for the orderId of the delivery which failed and return that orderId
        $query = "SELECT orderId FROM orders WHERE deliveryId = :deliveryId";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':deliveryId', $deliveryId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['orderId'] ?? null;
    }

    public function getOrderDetails($orderId){
        $query = "SELECT address, delLongitude, delLatitude, date FROM orders WHERE orderId = :orderId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':orderId', $orderId);

        if ($stmt->execute()){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }
}