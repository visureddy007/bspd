<?php
/*set_time_limit(0);
ini_set('memory_limit','2048M');*/
date_default_timezone_set('Asia/Kolkata');


/**
* ROUTES:
* (Authorization header with auth token is required for user session)
*
* /register
*   method - post
*   params - name, email, password
*
* /login
*   method - post
*   params - email, password
*
*/
require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require_once '../include/Utils.php';
require '../vendor/autoload.php';


$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');



/*if(SLIM_DEBUG){*/
  $app->config('debug',true);
/*}*/
/**
* route test block
*/
$app->get('/', function () use ($app) {
    echo "You are requesting ". substr($app->request->getUrl().$app->request->getRootUri(),0,-3);
   
});
/**
* User registration
* url - /register
* method - POST
* params - first_name, last_name, utype, email, password
*/
$app->post('/register', function() use ($app) {
  /*check for required params*/
  $requiredParams = array('Surname','Name','Alias','Gender','Year_Of_Birth','Gotram','Email_ID','Phone_Num','Referrer_ID','Location','BloodGroup','DOJ','Address1','Address2','City','State','Country');
  verifyRequiredParams($requiredParams);
  $response = array();
  /*reading post params*/
  $Surname = $app->request->post('Surname');
  $Name = $app->request->post('Name');
  $Alias = $app->request->post('Alias');
  $Gender = $app->request->post('Gender');
  $Year_Of_Birth = $app->request->post('Year_Of_Birth');
  $Gotram = $app->request->post('Gotram');
  $Email_ID = $app->request->post('Email_ID');
  $Phone_Num = $app->request->post('Phone_Num');
  $Referrer_ID = $app->request->post('Referrer_ID');
  $Location = $app->request->post('Location');
  $BloodGroup = $app->request->post('BloodGroup');
  $DOJ = $app->request->post('DOJ');
  $Address1 = $app->request->post('Address1');
  $Address2 = $app->request->post('Address2');
  $City = $app->request->post('City');
  $State = $app->request->post('State');
  $Country = $app->request->post('Country');
  if($Surname == null){
    $body = $app->request->getBody();
    $data = json_decode($body, true);
    $Surname = array_key_exists('Surname', $data) ? $data['Surname'] : null;
    $Name = array_key_exists('Name', $data) ? $data['Name'] : null;
	$Alias = array_key_exists('Alias', $data) ? $data['Alias'] : null;
	$Gender = array_key_exists('Gender', $data) ? $data['Gender'] : null;
	$Year_Of_Birth = array_key_exists('Year_Of_Birth', $data) ? $data['Year_Of_Birth'] : null;
	$Gotram = array_key_exists('Gotram', $data) ? $data['Gotram'] : null;
	$Email_ID = array_key_exists('Email_ID', $data) ? $data['Email_ID'] : null;
	$Phone_Num = array_key_exists('Phone_Num', $data) ? $data['Phone_Num'] : null;
	$Referrer_ID = array_key_exists('Referrer_ID', $data) ? $data['Referrer_ID'] : null;
	$Location = array_key_exists('Location', $data) ? $data['Location'] : null;
	$BloodGroup = array_key_exists('BloodGroup', $data) ? $data['BloodGroup'] : null;
	$DOJ = array_key_exists('DOJ', $data) ? $data['DOJ'] : null;
	$Address1 = array_key_exists('Address1', $data) ? $data['Address1'] : null;
	$Address2 = array_key_exists('Address2', $data) ? $data['Address2'] : null;
	$City = array_key_exists('City', $data) ? $data['City'] : null;
	$State = array_key_exists('State', $data) ? $data['State'] : null;
	$Country = array_key_exists('Country', $data) ? $data['Country'] : null;
  }
 
  /*validating email address*/
  validateEmail($Email_ID);
  $db = new DbHandler();
  $createdOn = date('Y-m-d H:i:s');
  $baseUrl = $app->request->getUrl().$app->request->getRootUri();
  $res_user = $db->createUser($Surname, $Name, $Alias,$Gender,$Year_Of_Birth,$Gotram,$Email_ID,$Phone_Num,$Referrer_ID,$Location,$BloodGroup,$createdOn);
  $MEMBER_ID = $res_user['insert_id'];
  $BSPD_Member_ID = $res_user['BSPD_Member_ID'];
  
 
 
  if ($res_user == CREATE_FAILED) {
      $response["error"] = true;
      $response["message"] = "Oops! An error occurred while registering";
      echoResponse(200, $response);
  } else if ($res_user == ALREADY_EXISTED) {
      $response["error"] = true;
      $response["message"] = "Sorry, this member already exist";
      echoResponse(200, $response);
  }else{
	  $res =  $db->createUserAddress($MEMBER_ID,$Alias,$DOJ,$Address1,$Address2,$City,$State,$Country);
      $response["error"] = false;
      $response["message"] = "You are successfully registered. Your Member Id is : ".$BSPD_Member_ID." ";
      $response["BSPD_Member_ID"] = $BSPD_Member_ID;
      echoResponse(201, $response); 
	  
  }
});
/**
* User Login
* url - /login
* method - POST
* params - Phone_Num, Password
*/
$app->post('/login', function() use ($app) {

  $requiredParams = array('Phone_Num', 'Password');
  $postdata = $app->request();
  verifyRequiredParams($requiredParams);
  
  /*print_r($postdata); exit;*/
  
  // reading post params
  $Phone_Num = $app->request()->post('Phone_Num');
  $Password = $app->request()->post('Password');

  if($Phone_Num == null)
  {
    $body = $app->request->getBody();
    $data = json_decode($body, true);
    
    $Phone_Num = $data['Phone_Num'];
    $Password = $data['Password'];
  }
   /*echo $employee_id.','. $Password;	*/
  $response = array();
  $db = new DbHandler();
  // check for correct employee_id and password
  if ($db->checkLogin($Phone_Num, $Password)) {
      // get the user by employee_id
      $user = $db->getUserByEmployeeId($Phone_Num); 
	  if ($user != NULL) {
        /*print_r($user);  */
          $response['token'] = bin2hex(openssl_random_pseudo_bytes(32));
          $response["error"] = false;
          $response['message'] = "Login successful.";
          $response['user_type'] = $user['MEMBER_TYPE'];
          $response['member_id'] = $user['MEMBER_ID'];
		  $response["BSPD_Member_ID"] = $user['BSPD_Member_ID'];
          $tokenExpiration = date('Y-m-d H:i:s', strtotime('+360 hour'));//the expiration date will be in one hour from the current moment
          $result = $db->updateToken($user['MEMBER_ID'], $response['token'], $tokenExpiration); 
        } else {
          // unknown error occurred
          $response['error'] = true;
          $response['message'] = "An error occurred. Please try again";
      }
  } else {
      // user credentials are wrong
      $response['error'] = true;
      $response['message'] = 'Login failed. Incorrect credentials or User id is not active.';
  }
  echoResponse(200, $response);
});

/**
* Events List
* url - /events
* method - GET
* params - NA
*/
$app->get('/events','authenticate',function() use($app){
	 $db = new DbHandler();
	 global $user_id;
	 $baseUrl = substr($app->request->getUrl().$app->request->getRootUri(),0,-3);
     $selectAry = array(
		'select'=>'e.*',
		'tbl_name'=>EVENTS_TBL.' as e where e.Event_status = 1'
	 );
	 $selectAryNot = array(
		'select'=>'e.*',
		'tbl_name'=>EVENTS_TBL.' as e where e.Event_status = 0'
	 );
	$completed_events = $db->getListByTblName($selectAry);
	$notcompleted_events = $db->getListByTblName($selectAryNot);
    $response["error"] = false;
    if(count($completed_events) > 0 || count($notcompleted_events) > 0)
    {
		$response["completed_events"] = $completed_events;
		$response["notcompleted_events"] = $notcompleted_events;
        $response["message"] = "Total Completed ".count($completed_events)." record(s), not completed ".count($notcompleted_events)." returned!";
    }else{
        $response["message"] = "No Records found!.";
    }
    
    echoResponse(200, $response);

});
/**
* Events List
* url - /events
* method - GET
* params - NA
*/
$app->get('/query',function() use($app){
	 $db = new DbHandler();
	
	$selectAry = array(
		'select'=>' GROUP_CONCAT(MEMBER_ID) from bspd_view_team_100',
		
	 );
	
	$completed_events = $db->getListByTblName($selectAry);

    $response["error"] = false;
    if(count($completed_events) > 0 )
    {
		$response["completed_events"] = $completed_events;
	
        $response["message"] = "Total Completed ".count($completed_events)." record(s),returned!";
    }else{
        $response["message"] = "No Records found!.";
    }
    
    echoResponse(200, $response);

});

/**
* Public Depedncy Data
* url - /commonData
* method - GET
* params - NA
*/
$app->get('/commonData',function() use($app){
	 $db = new DbHandler();
    $response["error"] = false;
	 $selectAry = array(
		'select'=>'n.*',
		'tbl_name'=>GOTRAS_TBL.' as n'
	);
	$gotras = $db->getListByTblName($selectAry);
	$response["gotras"] = $gotras;
     $selectAry = array(
		'select'=>'n.*',
		'tbl_name'=>DOJ_TBL.' as n'
	);
	$doj = $db->getListByTblName($selectAry);
	$response["doj"] = $doj;
	
	$selectAry = array(
		'select'=>'n.*',
		'tbl_name'=>LOC_TBL.' as n'
	);
	$locations = $db->getListByTblName($selectAry);
	$response["locations"] = $locations;
	
	$selectAry = array(
		'select'=>'u.MEMBER_ID,u.Surname,u.Name',
		'tbl_name'=>USERS_TBL.' as u'
	);
	$referenceMembers = $db->getListByTblName($selectAry);
	$response["referenceMembers"] = $referenceMembers;
    
    
    echoResponse(200, $response);

});

/**
* Post Complaint
* url - /complaints
* method - POST
* params - dl_name, dl_code,dl_pricefactor
*/
$app->post('/event_registration','authenticate',function() use ($app) {
	 $db = new DbHandler();
	// global $user_id;
	 $requiredParams = array('EVENT_ID', 'MEMBER_ID');
	verifyRequiredParams($requiredParams);
	
	$response = array();

	$EVENT_ID = $app->request->post('EVENT_ID');
	$MEMBER_ID = $app->request->post('MEMBER_ID');
	if($EVENT_ID == null)
	{
		$body = $app->request->getBody();
		$data = json_decode($body, true);
		$EVENT_ID     = $data['EVENT_ID'];
		$MEMBER_ID     = $data['MEMBER_ID'];
	}
	verifyRequiredParams($requiredParams);
	$db = new DbHandler();
	$res = $db->event_register($EVENT_ID,$MEMBER_ID);
	//print_r($res);exit;
	if ($res == CREATE_FAILED) {
	  $response["error"] = true;
	  $response["message"] = "Oops! An error occurred while creating";
	  echoResponse(200, $response);
	} else if ($res == ALREADY_EXISTED) {
      $response["error"] = true;
      $response["message"] = "Sorry, You have already been registered to this event";
      echoResponse(200, $response);
	} else{
		  //$res =  $db->event_register($EVENT_ID,$MEMBER_ID);
		  $response["error"] = false;
		  $response["message"] = "You are successfully registered";
		  echoResponse(201, $response); 
		  
	  }
});

/**
* get farmer details by farmer mobile no
* url - /memberSugg/:$Phone_Num
* method - GET
* params - $Phone_Num
*/
$app->get('/memberSugg/:BSPD_Member_ID','authenticate',function($BSPD_Member_ID) {
    $db = new DbHandler(); 
	$selectAry = array(
		'select'=>'u.Surname,u.Name,u.Gotram,u.MEMBER_ID',
		'tbl_name'=>USERS_TBL.' as u ',	
		'where'=>' u.`BSPD_Member_ID` = ?',			
		'bind_param'=>array('s'),		
		'values'=>array($BSPD_Member_ID)				
	);
	$result = $db->getListByTblName($selectAry);    
    $response["error"] = false;
    if(count($result) > 0)
    {
		$response["memberDet"] = $result;
        $response["message"] = "Total ".count($result)." record(s) returned!";
    }else{
        $response["message"] = "No Records found!.";
    }    
	echoResponse(200, $response);

});

// Check user email is already exists or not.
$app->get('/userexists/:email', function($email){
    $db = new DbHandler();
    $result =  $db->isUserExists($email);
    if($result)
    {
        $response["error"] = false;
        $response["message"] = "User found!.";
        echoResponse(200,$response);
    }else{
        $response["error"] = true;
        $response["message"] = "User not found!.";
        echoResponse(200,$response);
    }
});

/**
* Event registration for new member
* url - /register
* method - POST
* params - first_name, last_name, utype, email, password
*/
$app->post('/eveRegNewMem','authenticate', function() use ($app) {
  /*check for required params*/
  $requiredParams = array('Surname','Name','Gotram','Phone_Num','EVENT_ID');
  verifyRequiredParams($requiredParams);
  $response = array();
  /*reading post params*/
  $Surname = $app->request->post('Surname');
  $Name = $app->request->post('Name');
  $Gotram = $app->request->post('Gotram');
  $Phone_Num = $app->request->post('Phone_Num');
  $EVENT_ID = $app->request->post('EVENT_ID');
   if($Surname == null){
    $body = $app->request->getBody();
    $data = json_decode($body, true);
    $Surname = array_key_exists('Surname', $data) ? $data['Surname'] : null;
    $Name = array_key_exists('Name', $data) ? $data['Name'] : null;
	$Gotram = array_key_exists('Gotram', $data) ? $data['Gotram'] : null;
	$Phone_Num = array_key_exists('Phone_Num', $data) ? $data['Phone_Num'] : null;
	$EVENT_ID = array_key_exists('EVENT_ID', $data) ? $data['EVENT_ID'] : null;
  }
  $db = new DbHandler();
  $createdOn = date('Y-m-d H:i:s');
  $baseUrl = $app->request->getUrl().$app->request->getRootUri();
  $res_user = $db->createUserForEvent($Surname, $Name,$Gotram,$Phone_Num,$createdOn);
 // print_r($res_user);exit;
  $MEMBER_ID = $res_user['insert_id'];
   /*$res =  $db->createUserAddressEventReg($MEMBER_ID);*/
  // print_r($res);exit;
  if ($res_user == CREATE_FAILED) {
      $response["error"] = true;
      $response["message"] = "Oops! An error occurred while registering";
      echoResponse(200, $response);
  } else if ($res_user == ALREADY_EXISTED) {
      $response["error"] = true;
      $response["message"] = "Sorry, this member already exist";
      echoResponse(200, $response);
  }else{
	  $res =  $db->event_register($EVENT_ID,$MEMBER_ID);
      $response["error"] = false;
      $response["message"] = "You are successfully registered";
      echoResponse(201, $response); 
	  
  }
});


/* for changing event status
*/
$app->get('/eventStatusCornjob',function() use($app) {
	error_log('Cron');
	$db = new DbHandler();
	//$curr_date = "CURRDATE";
	$curr_date = date('Y-m-d');
	$ary = array(
		'select'=>'*',
		'tbl_name'=>EVENTS_TBL,	
		'set'=>'Event_status = 1',		
		'where'=>'Event_date < ?',		
		'bind_param'=>array('s'),		
		'values'=>array($curr_date)	
	);
	$res = $db->commonUpdate($ary);
	if ($res == CREATE_FAILED) {
		$response["error"] = true;
		$response["message"] = "Oops! An error occurred";
		echoResponse(200, $response);
	}else{
		$response["error"] = false;
		$response["message"] = "Successfully";
		echoResponse(201, $response);
	}
});

$app->run();
?>