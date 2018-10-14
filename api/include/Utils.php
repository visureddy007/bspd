<?php
//require '../vendor/autoload.php';
//require '../vendor/tcpdf/tcpdf.php';
require_once 'Config.php';
if(PHP_DEBUG_MODE){
  error_reporting(-1);
  ini_set('display_errors', 'On');
}
// authorized user id from db - global var
$user_id = NULL;
/**
* Verifying required params posted or not
*/
function verifyRequiredParams($required_fields) {
  $error = false;
  $error_fields = "";
  $request_params = array();
  $request_params = $_REQUEST;
  $app = \Slim\Slim::getInstance();
  /*
	  print_r($_REQUEST);		
	  print_r($_SERVER);		
	  print_r($_POST);
  */
  // handling PUT/PATCH request params
  if ($_SERVER['REQUEST_METHOD'] == 'PUT' || $_SERVER['REQUEST_METHOD'] == 'PATCH' || $_SERVER['REQUEST_METHOD'] == 'POST') {
	/*echo $_SERVER['REQUEST_METHOD']; 
	print_r($request_params);*/	
    if(empty($request_params) || (count($request_params)==1 && array_key_exists('_ga', $request_params)))
    {
         $request_params = json_decode($app->request()->getBody(), true);
    }
  }
  /*
	  print_r($request_params);
	  exit;
  */	
  /*error_log('required_fields:'.json_encode($required_fields));
  error_log('request_params:'.json_encode($request_params));*/
  foreach ($required_fields as $field) {
    if (!isset($request_params[$field]) || !is_array($request_params)) {
      $error = true;
      $error_fields .= $field . ', ';
    }
  }
  if ($error) {
    // required fields are missing or empty
    // echo error json and stop the app
    $response = array();
    $app = \Slim\Slim::getInstance();
    $response['error'] = true;
    $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
    error_log(echoResponse(400, $response));
    $app->stop();
  }
}

/**
* Validating email address
*/
function validateEmail($email){
  $app = \Slim\Slim::getInstance();
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['error'] = true;
    $response['message'] = 'Email is not valid';
    echoResponse(400, $response);
    $app->stop();
  }
}

/**
* Checking Tour Satisfied
*/
function tourSatisfied($tour){
	$tour_dates = $tour['tl_dates'];
	$tour_satisfied=false;
	if($tour_dates!=''){
		/*Preparing unique tour plan dates*/	
		$tour_datesA = explode(',',$tour_dates);			
		$tour_datesA = array_unique($tour_datesA);			
		
		/*Preparing unique tour days*/	
		$begin = new DateTime($tour['tour_start']);
		$end = new DateTime($tour['tour_end']);
		$daterange = new DatePeriod($begin, new DateInterval('P1D'), $end);
		$tour_days=array();
		foreach($daterange as $date){
			$tour_days[]= $date->format("Y-m-d");
		}
		$tour_days[] =$tour['tour_end'];
		/*print_r($tour_datesA);print_r($tour_days); */
		/*Getting unplaned tour dates*/
		$unplaned = array_diff($tour_days,$tour_datesA);
		/*print_r($unplaned);
		exit;*/
		if(count($unplaned)==0){
			$tour_satisfied=true;
		}					
	}
	return $tour_satisfied;	
}
/**
* Send Push Notification
*/
function sendNotification($deviceIds, $message){

  foreach ($deviceIds as &$token) {
	 /*print_r($token); continue;*/
	$msg = array();
	$msg['emp_id']=$token['emp_id'];
	if($message['title']=='TOUR_START'){
		$msg['action']='TOUR_START';		
	}else if($message['title']=='TOUR_END'){
		$msg['action']='TOUR_END';
	}
	$message['body']=json_encode($msg);
	/*error_log($token['fcmtoken'].':'.$message['body']);*/
	/*echo $token['fcmtoken'].' / '; continue;*/
    //FCM api URL
    $url = 'https://fcm.googleapis.com/fcm/send';
    //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
    /*$server_key = 'AIzaSyBUq-_LD48Cii0_QfW0N-Nm8VtlxqCclUM';*/
    $server_key = 'AAAApGHQqdo:APA91bEH0B2tSiLDlD3nPzOmjj-0zVIUzAFORPqv6ne1CU9BDHbKO3I3ffywkLreUyxXX5ORivWgKqrjJrv1-PleLGNFcVVPHtaZQ8rnnfFnLqsmgtyPhSmVr2DY3RewJD9YPnqDtWQI';
          
    $fields = array();
    /*$fields['notification'] = $message;*/
    $fields['data'] = $message;
   
      $fields['to'] = $token['fcmtoken'];
      $fields['priority'] = "high";

    //header with content_type api key
    $headers = array(
      'Content-Type:application/json',
      'Authorization:key='.$server_key
    );
          
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    //if ($result === FALSE) {
    //  die('FCM Send Error: ' . curl_error($ch));
    //}
    curl_close($ch);
	$resp = array(
		'FIELDS'=>$fields,
		'RESULT'=>$result
	);
	/*error_log(json_encode($resp));*/
  }

}

function sendNotification2($deviceIds, $message){

  foreach ($deviceIds as &$token) {

    //FCM api URL
    $url = 'https://fcm.googleapis.com/fcm/send';
    //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
    $server_key = 'AAAApGHQqdo:APA91bEH0B2tSiLDlD3nPzOmjj-0zVIUzAFORPqv6ne1CU9BDHbKO3I3ffywkLreUyxXX5ORivWgKqrjJrv1-PleLGNFcVVPHtaZQ8rnnfFnLqsmgtyPhSmVr2DY3RewJD9YPnqDtWQI';
          
    $fields = array();
    $fields['notification'] = $message;
   
      $fields['to'] = $token;
      $fields['priority'] = "high";

    //header with content_type api key
    $headers = array(
      'Content-Type:application/json',
      'Authorization:key='.$server_key
    );
          
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    //if ($result === FALSE) {
    //  die('FCM Send Error: ' . curl_error($ch));
    //}
    curl_close($ch);
	$resp = array(
		'FIELDS'=>$fields,
		'RESULT'=>$result
	);
	error_log(json_encode($resp));

  }

}



/**
* Send SMS 
*/
function sendSMS($message,$mobile)
{
	$url = 'https://alerts.solutionsinfini.com/api/v4/';	
	
	$params = array(
	'api_key'=>'Aa0df333ba902514a0fe866ec05267771',
	'method'=>'sms',
	'message'=>$message,
	'to'=>$mobile,
	'sender'=>'KSNKFT',
	);
	
	$postData = '';
   //create name value pairs seperated by &
   foreach($params as $k => $v) 
   { 
      $postData .= $k . '='.$v.'&'; 
   }
   $postData = rtrim($postData, '&');
	/*echo $postData; exit;*/
    $ch = curl_init();  
 
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HEADER, false); 
    curl_setopt($ch, CURLOPT_POST, count($postData));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);     
    $output=curl_exec($ch); 
	
    curl_close($ch);	
	
    return $output; 
}

/**
* PHP Mail
**/
function sendPhpMail($subject,$to,$message){
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: noreply@kisankraft.com Kisan Kraft';
	$headers .= 'BCC:  rajsubtest@gmail.com,acssriram@gmail.com'." \r\n";
	mail($to, $subject, $message, $headers);
}


/**
* Send Email 
*/
function sendEmail($subject, $from, $to, $body)
{
    $transport = Swift_SmtpTransport::newInstance('smtp.mailgun.org', 25)
        ->setUsername('postmaster@mg.readyteachers.com')
        ->setPassword('ec21031743271de2a96b7616a2921044');

        // Create the Mailer using your created Transport
        $mailer = Swift_Mailer::newInstance($transport);

        // Create a message
        $message = Swift_Message::newInstance($subject)
        ->setFrom($from)
        ->setTo($to)
        ->setBody($body,'text/html');

        // Send the message
        return $mailer->send($message);
}

function sendEmailCurl($subject, $from, $to, $body){
	 
		$config = array();
	 
		$config['api_key'] = "key-f1bb6f6bc02140f39cdcaf84c6add1e6";
	 
		$config['api_url'] = "https://api.mailgun.net/v3/mg.phloud.com/messages";
	 
		$message = array();
	 
		$message['from'] = "Phloud.com <postmaster@phloud.com>";
	 
		$message['to'] = $to;
	 
		$message['h:Reply-To'] = "&lt;info@phloud.com&gt;";
	 
		$message['subject'] = $subject;
	 
		$message['html'] = $html;
	 
		$ch = curl_init();
	 
		curl_setopt($ch, CURLOPT_URL, $config['api_url']);
	 
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	 
		curl_setopt($ch, CURLOPT_USERPWD, "api:{$config['api_key']}");
	 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	 
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	 
		curl_setopt($ch, CURLOPT_POST, true); 
	 
		curl_setopt($ch, CURLOPT_POSTFIELDS,$message);
	 
		$result = curl_exec($ch);
	 
		curl_close($ch);
	 
		return $result;
	 
	}


/**
* Validating zip code
*/
function validateZip($zip)
{
  $app = \Slim\Slim::getInstance();
  if (preg_match("#[0-9]{5}#", $zip) == 0) {
    $response['error'] = true;
    $response['message'] = 'ZipCode is not valid';
    echoResponse(400, $response);
    $app->stop();
  }
}


/**
* Validating user type
*/
function validateUserType($user_type){
  $app = \Slim\Slim::getInstance();
  if ($user_type != 'EMPLOYEE' && $user_type != 'SUPERVISOR' && $user_type != 'ADMIN') {
    $response['error'] = true;
    $response['message'] = 'User Type is not valid';
    echoResponse(400, $response);
    $app->stop();
  }
}

/**
* Echo json response
* @param String $status_code http response code
* @param Int $response Json response
*/
function echoResponse($status_code, $response) {
  $app = \Slim\Slim::getInstance();
  // Http response code
  $app->status($status_code);
  // setting response content type to json
  $app->contentType('application/json');
  $response = array_utf8_encode($response);
  $json =  json_encode($response,JSON_PRETTY_PRINT);
  if ($json)
    echo $json;
else
    echo json_last_error_msg();
}
/**
*  Adding Middle Layer to authenticate every request
*  Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
  $headers = apache_request_headers();
  $response = array();
  $app = \Slim\Slim::getInstance();
  if (isset($headers['Authorization'])) {
    $db = new DbHandler();
    $token = $headers['Authorization'];
	//print_r($token);exit;
    if (!$db->isValidApikey($token)) {
      $response['error'] = true;
      $response['message'] = 'Access denied. Invalid token key';
      echoResponse(401, $response);
      $app->stop();
    } else {
      global $user_id;
      $user = $db->getUserId($token);
      if ($user != NULL) {
        $user_id = $user['emp_uid'];
      }
    }
  } else {
    // token key is missing in header
    $response['error'] = true;
    $response['message'] = "auth key is missing";
    echoResponse(400, $response);
    $app->stop();
  }
}
/** Debugging utility */
function p($input, $exit=1) {
  echo '<pre>';
  print_r($input);
  echo '</pre>';
  if($exit) {
    exit;
  }
}
function j($input, $encode=true, $exit=1) {
  echo '<pre>';
  echo json_encode($input, JSON_PRETTY_PRINT | $encode);
  echo '</pre>';
  if($exit) {
    exit;
  }
}

/** array_search for multi-dimensional array**/
function multiDimArySearch($array,$value,$key){
	foreach($array as $index => $ary) {
		if(isset($ary[$key]) && $ary[$key] == $value)  return $index;
	}
    return NULL;
}

/**
* Write Log To Particular File
**/
function writeLogToFile($text,$file){
	$result=$text;	
	$current = file_get_contents($file);
	$current .= "[".date('d-m-Y H:i:s')."] : ".$result."\n";
	file_put_contents($file, $current);
}

/**
* Generate Pdf to folder
**/
function createPDF($path,$content){
	
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->AddPage();	
	$pdf->writeHTMLCell(0, 0, '', '', $content, 0, 1, 0, true, '', true);	
	$fname = $path;

	$pdf->Output('../../api'.$fname, 'F');
}

/**
 * Encode array to utf8 recursively
 * @param $dat
 * @return array|string
 */
function array_utf8_encode($dat)
{
    if (is_string($dat))
        return utf8_encode($dat);
    if (!is_array($dat))
        return $dat;
    $ret = array();
    foreach ($dat as $i => $d)
        $ret[$i] = array_utf8_encode($d);
    return $ret;
}
/*function array_utf8_encode2($dat)
{
    if (is_string($dat))
        return utf8_encode($dat);
    if (!is_array($dat))
        return $dat;
    $ret = array();
    foreach ($dat as $i => $d)
        $ret[$i] = array_utf8_encode2($d);
    return $ret;
}*/
?>