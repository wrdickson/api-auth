<?php

use \Firebase\JWT\JWT;

Class Jwt_Util {

  public static function generate($accountId) {
    $account = new Account($accountId);
    $payload = [
      'iat' => date("Y-m-d H:m:s", time()),
      'iss' => 'localhost',
      'exp' => time() + 86400,
      'exp_f' => date("Y-m-d H:m:s", time() + 86400),
      'accountId' => $accountId,
      'account' => $account->to_array()
    ];
    $token = JWT::encode($payload, JWT_KEY);
    return $token;
  }

  public static function validate($token) {

  }

  public static function validate_test($token) {
    //JWT::$leeway = 60; // $leeway in seconds
    try{
      return JWT::decode($token, JWT_KEY, array('HS256'));
    } catch (Exception $e){
      return $e->getMessage();
    }
  }
}