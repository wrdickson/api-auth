<?php

//  this is the db connection and global variable info . . . relocate up the tree for deployment
require 'config/config.php';

require 'php_classes/class.data_connecter.php';
require 'php_classes/class.account.php';
require 'php_classes/class.jwt_util.php';
require 'php_classes/class.validate.php';

require __DIR__ . '/vendor/autoload.php';

// set the timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

//  slim v2
$app = new \Slim\Slim();

//  ROUTES:

$app->post('/admin/create/', 'account_create');

$app->post('/account/login/', 'account_check_login');
$app->post('/account/testtoken/', 'account_test_token');

//  FUNCTIONS:

function account_create() {
  $app = \Slim\Slim::getInstance();
  $params = json_decode($app->request->getBody(true));
  $token = $params->token;
  //  validate user
  $username = $params->data->username;
  $email = $params->data->email;
  $password = $params->data->password;
  print Account::create_account($username, $password, $email);
}

function account_check_login() {
  $result = array();
  $app = \Slim\Slim::getInstance();
  $params = json_decode($app->request->getBody(true));
  $username = $params->username;
  $password = $params->password;

  //  validate, $valid will be an array
  $valid = Validate::validate_login( array('username' => $username, 'password' => $password) );
  //  if valid, go ahead with the db check
  if($valid['is_valid'] == true) {
    $result = Account::check_login($username, $password);
    if($result['pass'] == true && $result['account_id'] > 0) {
      // and this is where we generate the json web token
      $output = Jwt_Util::generate($result['account_id']);
      $result['jwt'] = $output;
    }
  } else {
    $result = array( 'pass' => false );
  }
  echo json_encode(array_merge($result, $valid));
}

function account_test_token() {
  $result = array();
  $app = \Slim\Slim::getInstance();
  $params = json_decode($app->request->getBody(true));
  $jwt = $params->jwt;
  $test = Jwt_Util::validate_token($jwt);
  echo json_encode($test);
}

$app->run();
