<?php
//  this is the db connection info  . . . relocate up the tree for deployment
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

$app->post('/admin/account/', 'account_admin_create');
$app->post('/admin/validate_token', 'account_admin_validate_token');
$app->get('/admin/validate_test', 'account_admin_validate_test');

$app->post('/account/login/', 'account_check_login');

//  FUNCTIONS:

function account_admin_create() {
  $app = \Slim\Slim::getInstance();
  $params = json_decode($app->request->getBody(true));
  $username = $params->data->username;
  $email = $params->data->email;
  $password = $params->data->password;
  print Account::create_account($username, $password, $email);
}

function account_admin_validate_test() {
  echo Jwt_Util::validate_test();
}

function account_admin_validate_token() {
  $app = \Slim\Slim::getInstance();
  $params = json_decode($app->request->getBody(true));
  $token = $params->data->token;
  echo Jwt::validate($token);
}

function account_check_login() {
  $result = array();
  $app = \Slim\Slim::getInstance();
  $params = json_decode($app->request->getBody(true));
  $username = $params->data->username;
  $password = $params->data->password;
  //  validate, $is_valid will be an array
  $is_valid = Validate::validate_login( array('username' => $username, 'password' => $password) );
  //  if valid, go ahead with the db check
  if($is_valid['is_valid'] == true) {
    $result = Account::check_login($username, $password);
    if($result['pass'] == true && $result['accountId'] > 0) {
      $output = Jwt_Util::generate($result['accountId']);
      $result['jwt'] = $output;
    }
  } 
  echo json_encode(array_merge($result, $is_valid));
}

$app->run();
