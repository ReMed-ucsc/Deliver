<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

//namespaces 

class JWTHandler {
    private static $secret_key = 'helloworld';
    private static $algoritm = 'HS256';

    public static function encode($data){
        return JWT::encode($data, self::$secret_key, self::$algoritm);
    }

    public static function decode($jwt){
        return JWT::decode($jwt, new Key(self::$secret_key, self::$algoritm));
    }
}

//not the singelton pattern