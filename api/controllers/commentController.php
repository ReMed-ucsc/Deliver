<?php

include_once __DIR__ . '/../models/Comment.php';
include_once __DIR__ . '/../config/Database.php';

class CommentController{
    private $db;
    private $comment;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
        $this->comment = new Comments($this->db);
    }

    public function addNewComment($data){
        if(!empty($data['driverId']) && !empty($data['orderId']) && !empty($data['comment'])){
            $this->comment->orderId = $data['orderId'];
            $this->comment->driverId = $data['driverId'];
            $this->comment->comment = $data['comment'];

            $response = $this->comment->addComment();

            return $response;
        }
    }

    public function updateExistingComment($data){
        if(!empty($data['commentId']) && !empty($data['comment'])){
            $this->comment->commentId = $data['commentId'];
            $this->comment->comment = $data['comment'];

            if($this->comment->searchComment()){
                return $this->comment->updateComment();
            } else {
                return [
                    "status" => "error",
                    "message" => "no commetn was found"
                ];
            }
        }
    }

    public function deleteExistingComment($data){
        if(!empty($data['commnetId'])){
            if($this->comment->searchComment()){
                $this->comment->deleteComment();
            } else {
                return [
                    "status" => "error",
                    "message" => "no commetn was found"
                ];
            }
        }
    }

    public function getCommentsOfDriver($data){
        if(!empty($data['driverId'])){
            $this->comment->driverId = $data['driverId'];
            return $this->comment->searchCommentForDriver();
        }else{
            return [
                "status" => "error",
                "message" => "driverId didn't passed"
            ];
        }
    }

    public function getOneComment($data){
        if(!empty($data['commentId'])){
            $this->comment->commentId = $data['commentId'];
            return $this->comment->getOneComment();
        } else {
            return [
                "status" => "error",
                "message" => "commentId not found"
            ];
        }
    }
}