<?php
/******************************************************************************
 *
 * Filename: func.php
 * Purpose: Holds all of the functions that are commonly used by more than one
 *          page. ex: setting up the database connection with mysqlSetup(&db)
 *
 *****************************************************************************/
if(!defined('SM_PATH'))
	define('SM_PATH','../');
//Should be absolute.. 
//define('SM_PATH','/home/soemail/html/');

require_once(SM_PATH . 'functions/display_messages.php');
require_once(SM_PATH . "facebook/facebook.php");

//MySQL Database Info
$dbHost = "127.0.0.1:3307";
$dbUsername = "trantho";
$dbPassword = "db4trantho";
$dbName = "trantho";

//Facebook API variables
$appapikey = '320068457204cf9d35b581a998599155';
$appsecret = '8650efcf689d47df423040f4504642bd';
$facebook = new Facebook($appapikey, $appsecret);

$faith_application_id = 30;

//Setting up the client, specifying the server.
$soapClient = new SoapClient(null, array('location' => "http://cyrus.cs.ucdavis.edu/~dslfaith/faith/soap.php",
                                     'uri'      => "urn://cyrus.cs.ucdavis.edu/req",
                                     'trace'    => 1));

/******************************************************************************
 *
 * Purpose: Sets up the database connection.
 * Takes: &db: the variable will modified to be the database connection.
 * Returns: none
 *
 *****************************************************************************/
function mysqlSetup(&$db) {
  global $dbHost, $dbUsername, $dbPassword, $dbName;
  if($dbPassword==""){
    $db = mysql_connect($dbHost, $dbUsername);
  } else {
    $db = mysql_connect($dbHost, $dbUsername, $dbPassword);
  }
  mysql_select_db($dbName,$db); //Specify our database (trantho)
}

function getUserInfo($email) {
  global $imapServerName, $color;
  mysqlSetup($db);

  if(strpos($email,"@")===FALSE){//Did not append @servername.com to email.
    if($imapServerName=="Gmail"){
      $email .= "@gmail.com";
    }
  }

  $query = "SELECT fbUserId FROM email_settings where email='" . $email . "' AND service='" . $imapServerName . "'";

  $result = mysql_query($query,$db);
  if($result!= NULL){
    $userInfo = mysql_fetch_row($result);
    $fbUserID = $userInfo[0];

    return $fbUserID;
  } else {
    error_box("User does not exist in DSL. Try <a href='http://apps.facebook.com/cyrusdsl_php/soemail.php'>registering</a> first.", $color);
  }
}

function getUserIdFromEmail($email) {
  global $imapServerName;
  mysqlSetup($db);

  if(strpos($email,"@")===FALSE){//Did not append @servername.com to email.
    if($imapServerName=="Gmail"){
      $email .= "@gmail.com";
    }
  }

  $query = "SELECT fbUserId FROM email_settings where email='" . $email . "'";

  $result = mysql_query($query,$db);
  if($result!= NULL){
    $userInfo = mysql_fetch_row($result);
    $fbUserID = $userInfo[0];

    return $fbUserID;
  } else {
    return -1;
  }
}

function getPicFromFacebook($uid){
  global $facebook;
  $pic = $facebook->api_client->users_getInfo($uid, 'pic_square');
  if(empty($pic[0]['pic_square'])){
    return 'http://static.ak.fbcdn.net/pics/q_silhouette.gif';
  } else {
    return $pic[0]['pic_square'];
  }
}

function getPic($uid,$username=''){
  global $soapClient;
  global $faith_application_id;
  
  if($username == '')
  {
  	sqgetGlobalVar('username',  $username,   SQ_SESSION);
  }
  
  $current_uid = $username;
  
  if(!is_numeric($current_uid))
  {
  	$current_uid = getUserIdFromEmail($username);
  }
  
  return $soapClient->__soapCall("getPic",array($uid, $current_uid, $_SERVER['REMOTE_ADDR'], $faith_application_id));
}

function findSocialPath($sender,$recipient,$username=''){
  global $soapClient;
  global $faith_application_id;
  
  if($username == '')
  {
  	sqgetGlobalVar('username',  $username,   SQ_SESSION);
  }
  
  $current_uid = $username;
  
  if(!is_numeric($current_uid))
  {
  	$current_uid = getUserIdFromEmail($username);
  }
  
  $response = $soapClient->__soapCall("findSocialPath",array($sender,$recipient, $current_uid, $_SERVER['REMOTE_ADDR'], $faith_application_id));
	return $response;
}


function findMultipleSocialPaths($sender,$recipient,$username=''){
  global $soapClient;
  global $faith_application_id;
  
  if($username == '')
  {
  	sqgetGlobalVar('username',  $username,   SQ_SESSION);
  }
  
  $current_uid = $username;
  
  if(!is_numeric($current_uid))
  {
  	$current_uid = getUserIdFromEmail($username);
  }
  
  return $soapClient->__soapCall("findMultipleSocialPaths",array($sender,$recipient, $current_uid, $_SERVER['REMOTE_ADDR'], $faith_application_id));
}

function uidToName($uid,$username=''){
  global $soapClient;
  global $faith_application_id;
  
 if($username == '')
  {
  	sqgetGlobalVar('username',  $username,   SQ_SESSION);
  }
  
  $current_uid = $username;
  
  if(!is_numeric($current_uid))
  {
  	$current_uid = getUserIdFromEmail($username);
  }
  
  return $soapClient->__soapCall("uidToName",array($uid, $current_uid, $_SERVER['REMOTE_ADDR'], $faith_application_id));
}

function sendSocialMessage($path,$username='') {
  global $soapClient;
  global $faith_application_id;
  
  if($username == '')
  {
  	sqgetGlobalVar('username',  $username,   SQ_SESSION);
  }
  
  $current_uid = $username;
  
  if(!is_numeric($current_uid))
  {
  	$current_uid = getUserIdFromEmail($username);
  }
  
  return $soapClient->__soapCall("sendMessage",array($path, $current_uid, $_SERVER['REMOTE_ADDR'], $faith_application_id));
}

function setOutcome($path,$outcome,$username='') {
  global $soapClient;
  global $faith_application_id;
  
  if($username == '')
  {
  	sqgetGlobalVar('username',  $username,   SQ_SESSION);
  }
  
  $current_uid = $username;
  
  if(!is_numeric($current_uid))
  {
  	$current_uid = getUserIdFromEmail($username);
  }
  
  return $soapClient->__soapCall("setOutcome",array($path,$outcome, $current_uid, $_SERVER['REMOTE_ADDR'], $faith_application_id));
}
