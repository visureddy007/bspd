<?php
class DbHandler {
	private $conn;
	private $superemployees;
	function __construct() {
		require_once dirname(__FILE__) . '/DbConnect.php';
		//opening db connection
		$db = new DbConnect();
		$this->conn = $db->connect();
		$this->superemployees=array();
	}
	
	public function AI($tbl){	
		
		$stmt = $this->conn->prepare("SHOW TABLE STATUS LIKE '$tbl'");
		$stmt->execute();
		
		/*$result = $stmt->get_result();
		$data = array();
		while ( $row = $result->fetch_array(MYSQLI_ASSOC) ) {
			$data[] = $row;
		}
		return $data[0]['Auto_increment'];*/
		$data = array();
		$meta = $stmt->result_metadata(); 
		while ($field = $meta->fetch_field()) { 
			$params[] = &$row[$field->name]; 
		} 
		call_user_func_array(array($stmt, 'bind_result'), $params); 
		while ($stmt->fetch()) { 
			foreach($row as $key => $val) { 
				$c[$key] = $val; 
			} 
			$data = $c; 
		}
		return $data['Auto_increment'];
	}	
	
	
	
	/* ------------- users table method ------------------ */
	/**     
	* Creating new user     
	* @param String $name User full name     
	* @param String $email User login email id     
	* @param String $password User login password     
	*/
	public function createUser_bckp($Surname, $Name, $Alias,$Gender,$Year_Of_Birth,$Gotram,$Email_ID,$Phone_Num,$Referrer_ID,$Location,$BloodGroup,$createdOn){
		require_once 'PassHash.php';
		$response = array();
		$this->conn->autocommit(false);
		$password = $Phone_Num;
		//$createdon = date('Y-m-d H:i:s');
		$AI = $this->AI(USERS_TBL);
		$BSPD_Member_ID= str_pad($AI, 4, "0", STR_PAD_LEFT);     		
		$BSPD_Member_ID='MA'.$BSPD_Member_ID;	 
		/* First check if user already existed in db */
		if (!$this->isUserExists($Phone_Num)) {
			/*Generating password hash*/
			$password_hash = PassHash::hash($password);
			$sql = "INSERT INTO bspd_member(Surname,Name,Alias,Gender,Year_Of_Birth,Gotram,Email_ID,Phone_Num,Referrer_ID,Location,BloodGroup,created,Password,BSPD_Member_ID) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";		
			/*echo $sql; exit;*/
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("ssssssssssssss",$Surname,$Name,$Alias,$Gender,$Year_Of_Birth,$Gotram,$Email_ID,$Phone_Num,$Referrer_ID,$Location,$BloodGroup,$createdOn,$password_hash,$BSPD_Member_ID);
			$result = $stmt->execute();
			/*printf("Error: %s.\n", $stmt->error);*/
			$stmt->close();
			/*Check for successful insertion*/
			if ($result) {
				 $this->conn->commit();
				return CREATED_SUCCESSFULLY;
			}
			else {
				
				return CREATE_FAILED;
			}
			
		}
		else {
			/*User with same email already existed in the db*/
			return ALREADY_EXISTED;
		}
		return $response;
	}
	
	/* ------------- users table method ------------------ */
	/**     
	* Creating new user     
	* @param String $name User full name     
	* @param String $email User login email id     
	* @param String $password User login password     
	*/
	public function createUserAddress($MEMBER_ID,$Alias,$DOJ,$Address1,$Address2,$City,$State,$Country){
		
		$response = array();
		$this->conn->autocommit(false);
			$sql = "INSERT INTO BSPD_Address_mobile(MEMBER_ID,Alias,DOJ,Address1,Address2,City,State,Country) values(?,?,?,?,?,?,?,?)";		
			//echo $sql; exit;
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("ssssssss",$MEMBER_ID,$Alias,$DOJ,$Address1,$Address2,$City,$State,$Country);
			$result = $stmt->execute();
		//printf("Error: %s.\n", $stmt->error);
			$stmt->close();
			/*Check for successful insertion*/
			if ($result) {
				 $this->conn->commit();
				return CREATED_SUCCESSFULLY;
			}
			else {
				
				return CREATE_FAILED;
			}
			
		
		
		return $response;
	}
	/* ------------- users table method ------------------ */
	/**     
	* Creating new user     
	* @param String $name User full name     
	* @param String $email User login email id     
	* @param String $password User login password     
	*/
	public function createUserAddressEventReg($MEMBER_ID){
		
		$response = array();
		$this->conn->autocommit(false);
			$sql = "INSERT INTO BSPD_Address_mobile(MEMBER_ID) values(?)";		
			//echo $sql; exit;
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("s",$MEMBER_ID);
			$result = $stmt->execute();
		//printf("Error: %s.\n", $stmt->error);
			$stmt->close();
			/*Check for successful insertion*/
			if ($result) {
				 $this->conn->commit();
				return CREATED_SUCCESSFULLY;
			}
			else {
				
				return CREATE_FAILED;
			}
			
		
		
		return $response;
	}
	
	
	public function createUser($Surname, $Name, $Alias,$Gender,$Year_Of_Birth,$Gotram,$Email_ID,$Phone_Num,$Referrer_ID,$Location,$BloodGroup,$createdOn){
		$selectAry = array(
				'select'=>'m.Phone_Num',
				'tbl_name'=>USERS_TBL.' as m ',		
				'where'=>' m.Phone_Num =? ',		
				'join'=>'',		
				'bind_param'=>array('s'),		
				'values'=>array($Phone_Num)		
			);
		$de = $this->getListByTblName($selectAry);
		$response = array();
		if(count($de) == 0){
		$this->conn->autocommit(false);
		$password = $Phone_Num;
		//$createdon = date('Y-m-d H:i:s');
		$AI = $this->AI(USERS_TBL);
		$BSPD_Member_ID= str_pad($AI, 4, "0", STR_PAD_LEFT);     		
		$BSPD_Member_ID='MA0000'.$BSPD_Member_ID;	 
		//$createdon = date('Y-m-d H:i:s');
		//$password_hash = PassHash::hash($password);	
		$password_hash = md5($password);	
		$sql = "INSERT INTO bspd_member(Surname,Name,Alias,Gender,Year_Of_Birth,Gotram,Email_ID,Phone_Num,Referrer_ID,Location,BloodGroup,created,Password,BSPD_Member_ID) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";		
		//echo $sql; exit;
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("ssssssssssssss",$Surname,$Name,$Alias,$Gender,$Year_Of_Birth,$Gotram,$Email_ID,$Phone_Num,$Referrer_ID,$Location,$BloodGroup,$createdOn,$password_hash,$BSPD_Member_ID);
		$result = $stmt->execute();
		//print_r($result);exit;
	    printf("Error: %s.\n", $stmt->error);
		if ($result){
			 $insert_id = $this->conn->insert_id;
			 $this->conn->commit();
			return array('insert_id'=>$insert_id,'BSPD_Member_ID'=>$BSPD_Member_ID);
		}else {
			
			return CREATE_FAILED;
		}
		}else{
			return ALREADY_EXISTED;
		}

	}
	public function createUserForEvent($Surname, $Name,$Gotram,$Phone_Num,$createdOn){
		$selectAry = array(
				'select'=>'m.Phone_Num',
				'tbl_name'=>USERS_TBL.' as m ',		
				'where'=>' m.Phone_Num =? ',		
				'join'=>'',		
				'bind_param'=>array('s'),		
				'values'=>array($Phone_Num)		
			);
		$de = $this->getListByTblName($selectAry);
		//print_r(count($de));exit;
		$response = array();
		if(count($de) == 0){	
			$this->conn->autocommit(false);
			$password = $Phone_Num;
			$AI = $this->AI(USERS_TBL);
			$BSPD_Member_ID= str_pad($AI, 4, "0", STR_PAD_LEFT);     		
			$BSPD_Member_ID='MA0000'.$BSPD_Member_ID;	 
			$password_hash = md5($password);	
			$sql = "INSERT INTO bspd_test_users(Surname,Name,Gotram,Phone_Num,created,Password,BSPD_Member_ID) values(?,?,?,?,?,?,?)";		
			/*echo $sql; exit;*/
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("sssssss",$Surname,$Name,$Gotram,$Phone_Num,$createdOn,$password_hash,$BSPD_Member_ID);
			$result = $stmt->execute();
			//print_r($result);exit;
			/*printf("Error: %s.\n", $stmt->error);*/
				if ($result){
					 $insert_id = $this->conn->insert_id;
					 $this->conn->commit();
					return array('insert_id'=>$insert_id,'CREATED_SUCCESSFULLY'=>CREATED_SUCCESSFULLY);
				}else{
					return CREATE_FAILED;
				}
			}else{
				return ALREADY_EXISTED;
			}
				return $response;
	}
	
	public function getListByTblName($qryAry){
		$sql = 'SELECT '.$qryAry['select'];
		
		
		
		if(isset($qryAry['tbl_name']) && $qryAry['tbl_name']!=''){
			
			$sql.= ' FROM '.$qryAry['tbl_name'];
		}
		if(isset($qryAry['join']) && $qryAry['join']!=''){
			$sql.=$qryAry['join'];
		}	
	//	echo $sql; exit;
		if(isset($qryAry['where']) && $qryAry['where']!=''){
			$sql.=' WHERE '.$qryAry['where'];
			$stmt = $this->conn->prepare($sql);
			$a_param_type = $qryAry['bind_param'];
			$a_bind_params = $qryAry['values'];
			
			$a_params = array(); 
			$param_type = '';
			$n = count($a_param_type);
			for($i = 0; $i < $n; $i++) {
			  $param_type .= $a_param_type[$i];
			}
			 
			/* with call_user_func_array, array params must be passed by reference */
			$a_params[] = & $param_type;
			 
			for($i = 0; $i < $n; $i++) {
			  /* with call_user_func_array, array params must be passed by reference */
			  $a_params[] = & $a_bind_params[$i];
			}
			/* use call_user_func_array, as $stmt->bind_param('s', $param); does not accept params array */
			/*print_r($a_params); exit;*/
			/*
			echo $sql; exit;
			*/
			/*error_log($sql);*/
			call_user_func_array(array($stmt, 'bind_param'), $a_params);
 
		}else{
				/*
				echo $sql; exit;
				*/
			$stmt = $this->conn->prepare($sql);
		}
		$stmt->execute();
		$data = array();
		/*$result = $stmt->get_result();
		while ( $row = $result->fetch_array(MYSQLI_ASSOC) ) {
			$data[] = $row;
		}
		return $data;*/
		$meta = $stmt->result_metadata(); 
		while ($field = $meta->fetch_field()) { 
			$params[] = &$row[$field->name]; 
		} 
		call_user_func_array(array($stmt, 'bind_result'), $params); 
		while ($stmt->fetch()) { 
			foreach($row as $key => $val) { 
				$c[$key] = $val; 
			} 
			$data[] = $c; 
		}
		return $data;
	}
	
	public function commonUpdate($qryAry){
		$sql = 'UPDATE '.$qryAry['tbl_name'];
		
		/*echo $sql; exit;*/
		if(isset($qryAry['where']) && $qryAry['where']!=''){
			$sql.=' SET '.$qryAry['set'];
			$sql.=' WHERE '.$qryAry['where'];
			$stmt = $this->conn->prepare($sql);
			$a_param_type = $qryAry['bind_param'];
			$a_bind_params = $qryAry['values'];
			
			$a_params = array(); 
			$param_type = '';
			$n = count($a_param_type);
			for($i = 0; $i < $n; $i++) {
			  $param_type .= $a_param_type[$i];
			}
			 
			/* with call_user_func_array, array params must be passed by reference */
			$a_params[] = & $param_type;
			 
			for($i = 0; $i < $n; $i++) {
			  /* with call_user_func_array, array params must be passed by reference */
			  $a_params[] = & $a_bind_params[$i];
			}
			/* use call_user_func_array, as $stmt->bind_param('s', $param); does not accept params array */
			/*print_r($a_params); exit;*/
			/*
			echo $sql; exit;
			*/
			call_user_func_array(array($stmt, 'bind_param'), $a_params);
 
		}
		$result = $stmt->execute();
		/*printf("Error: %s.\n", $stmt->error);*/
		$stmt->close();
		//	Check for successful insertion
		if ($result) {
			 $this->conn->commit();
			//	User successfully inserted
			return CREATED_SUCCESSFULLY;
		}
		else {
			
			return CREATE_FAILED;
		}
		
		return $response;
	}
	
	/**     
	* Creating new complaint     
	*/
	public function event_register($EVENT_ID,$MEMBER_ID){
		$response = array();
		$selectAry = array(
				'select'=>'e.MEMBER_ID,e.EVENT_ID',
				'tbl_name'=>EVENTS_TBL_REGIS.' as e ',		
				'where'=>' e.MEMBER_ID =? AND e.EVENT_ID =? ',		
				'join'=>'',		
				'bind_param'=>array('s','s'),		
				'values'=>array($MEMBER_ID,$EVENT_ID)		
		);
		$de = $this->getListByTblName($selectAry);
		//print_r($de);exit;
		if(count($de) == 0){
		$this->conn->autocommit(false);
		$Registered_Date = date('Y-m-d H:i:s');
		$registered = "Y";
		$Attended = "N";
		$sql = "INSERT INTO BSPD_Event_Registration(MEMBER_ID,EVENT_ID,Registered,Attended,Registered_Date) values(?,?,?,?,?)";	$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("sssss",$MEMBER_ID,$EVENT_ID,$registered,$Attended,$Registered_Date);
		$result = $stmt->execute();
		//print_r($result);exit;
		//printf("Error: %s.\n", $stmt->error); exit;
		$stmt->close();
		//	Check for successful insertion
		if ($result) {
			 $this->conn->commit();
			//	User successfully inserted
			return CREATED_SUCCESSFULLY;
		}
		else {
			
			return CREATE_FAILED;
		}
		}else{
				return ALREADY_EXISTED;
		}
			
			
			return $response;
	}
	
	/**     
	* Creating new complaint     
	*/
	public function createUrl($url_title,$url_subject,$url_name,$url_notes,$url_date,$url_time,$user_id){
		$response = array();			
		$this->conn->autocommit(false);
		$created_on = date('Y-m-d H:i:s');
		$sql = "INSERT INTO rz_url(url_title,url_subject,url_name,url_notes,url_date,url_time,status,user_id,created_on) values(?,?,?,?,?,?,1,?,?)";		
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("ssssssis",$url_title,$url_subject,$url_name,$url_notes,$url_date,$url_time,$user_id,$created_on);
			
		$result = $stmt->execute();
		/*printf("Error: %s.\n", $stmt->error);*/
		$stmt->close();
		//	Check for successful insertion
		if ($result) {
			 $this->conn->commit();
			//	User successfully inserted
			return CREATED_SUCCESSFULLY;
		}
		else {
			
			return CREATE_FAILED;
		}
			
			
			return $response;
	}
	/**     
	* Updating Complaint   
	*/
	public function updateUrl($url_id,$url_title,$url_subject,$url_name,$url_notes,$url_date,$url_time,$status){
			$response = array();
			$this->conn->autocommit(false);		
			$sql = "UPDATE rz_url SET url_title=?,url_subject=?,url_name=?,url_notes=?,url_date=?,url_time=?,status=? WHERE url_id=?";		
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("ssssssii",$url_title,$url_subject,$url_name,$url_notes,$url_date,$url_time,$status,$url_id);
			$result = $stmt->execute();
			/*printf("Error: %s.\n", $stmt->error);*/
			$stmt->close();
			//	Check for successful insertion
			if ($result) {
				 $this->conn->commit();
				//	User successfully inserted
				return CREATED_SUCCESSFULLY;
			}
			else {
				
				return CREATE_FAILED;
			}
		return $response;
	}
	
	public function deleteUrl($user_id,$url_id){
		
		$response = array();
		$this->conn->autocommit(false);
	    $sql = "DELETE FROM rz_url WHERE user_id=? AND url_id=?";		

			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("ii",$user_id ,$url_id);
				
			$result = $stmt->execute();
			/*printf("Error: %s.\n", $stmt->error);*/
			$stmt->close();
			//	Check for successful insertion
			if ($result) {
				 $this->conn->commit();
				//	User successfully inserted
				return CREATED_SUCCESSFULLY;
			}
			else {
				
				return CREATE_FAILED;
			}
		
		return $response;
	}
	
	
	 /**     
	* Checking for duplicate user by email address     
	* @param String $email email to check in db     
	* @return boolean     
	*/
	
	public function isUserExists($Phone_Num) {
		$sql = "SELECT MEMBER_ID FROM ".USERS_TBL." WHERE Phone_Num = ?";
		/*echo $sql; exit;*/
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("s", $Phone_Num);
		$stmt->execute();
		$stmt->store_result();
		$num_rows = $stmt->num_rows;
		$stmt->close();
		return $num_rows > 0;
		
	}
	/**     
	* Checking user login     
	* @param String $email User login email id     
	* @param String $password User login password     
	* @return boolean User login status success/fail     
	*/
	public function checkLogin($Phone_Num, $Password) {
		
		//fetching user by employee_id
		$stmt = $this->conn->prepare("SELECT Password FROM ".USERS_TBL." WHERE Phone_Num = ? OR BSPD_Member_ID = ?");
		$stmt->bind_param("ss", $Phone_Num,$Phone_Num);
		$stmt->execute();
		$stmt->bind_result($password_hash);
		$stmt->store_result();
		if ($stmt->num_rows > 0) {
			//	Found user with the employee_id
			//	Now verify the password
			$stmt->fetch();
			$stmt->close();
			if (PassHash::check_password($password_hash, $Password)) {
				//		User password is correct
				return TRUE;
			}
			else {
				//		user password is incorrect
				return FALSE;
			}
		}
		else {
			$stmt->close();
			//	user not existed with the employee_id
			return FALSE;
		}
	}
	
	public function updateToken($emp_uid,$token,$token_expire){
		
		$response = array();
		$createdon = date('Y-m-d H:i:s');
		/*$stmt = $this->conn->prepare("UPDATE ".EMPLOYEES_TBL." SET token = ?, token_expire = ? WHERE emp_uid = ?");*/
		$stmt = $this->conn->prepare("INSERT INTO bspd_tokens(emp_uid,token,token_expire,createdon) VALUES(?,?,?,?)");
		$stmt->bind_param("ssss", $emp_uid,$token, $token_expire,$createdon);
		$stmt->execute();
		$stmt->close();
		$this->conn->commit();
		return $response;
	}
	
	/**     
	* Fetching user id by api key     
	* @param String $api_key user api key     
	*/
	public function getUserId($api_key) {
		/*$stmt = $this->conn->prepare("SELECT emp_id FROM kk_employees WHERE token = ?");*/
		$stmt = $this->conn->prepare("SELECT t.emp_uid FROM bspd_tokens as t JOIN  bspd_test_users as u ON  u.MEMBER_ID=t.emp_uid  WHERE t.token = ?");
		$stmt->bind_param("s", $api_key);
		if ($stmt->execute()) {
			/*$user_id = $stmt->get_result()->fetch_assoc();
			$stmt->close();
			return $user_id;*/
			$meta = $stmt->result_metadata(); 
			while ($field = $meta->fetch_field()) { 
				$params[] = &$row[$field->name]; 
			} 
			call_user_func_array(array($stmt, 'bind_result'), $params); 
			while ($stmt->fetch()) { 
				foreach($row as $key => $val) { 
					$c[$key] = $val; 
				} 
				$user = $c; 
			}
			/*print_r($user); */
			$stmt->close();
			return $user;
		}
		else {
			return NULL;
		}
	}
	
	/**     
	* Validating user api key     
	* If the api key is there in db, it is a valid key     
	* @param String $api_key user api key     
	* @return boolean     
	*/
	public function isValidApikey($api_key) {
		$tokenExpiration = date('Y-m-d H:i:s');
		/*$stmt = $this->conn->prepare("SELECT emp_id from kk_employees WHERE token = ? AND token_expire >= ?");*/
		$stmt = $this->conn->prepare("SELECT t.emp_uid from bspd_tokens as t JOIN bspd_test_users as u ON  u.MEMBER_ID=t.emp_uid WHERE t.token = ? AND t.token_expire >= ?");
		$stmt->bind_param("ss", $api_key,$tokenExpiration);
		$stmt->execute();
		$stmt->store_result();
		$num_rows = $stmt->num_rows;
		$stmt->close();
		return $num_rows > 0;
	}
	
	/**     
	* Fetching user by email     
	* @param String $email User email id     
	*/
	
	public function getUserByEmployeeId($Phone_Num) {
		$stmt = $this->conn->prepare("SELECT * FROM ".USERS_TBL." WHERE Phone_Num = ? OR BSPD_Member_ID = ?");
		$stmt->bind_param("ss", $Phone_Num,$Phone_Num);
		if ($stmt->execute()) {
			/*$user = $stmt->get_result()->fetch_assoc();
			$stmt->close();
			return $user;*/
			$meta = $stmt->result_metadata(); 
		    while ($field = $meta->fetch_field()) { 
		        $params[] = &$row[$field->name]; 
		    } 
		    call_user_func_array(array($stmt, 'bind_result'), $params); 
		    while ($stmt->fetch()) { 
		        foreach($row as $key => $val) { 
		            $c[$key] = $val; 
		        } 
		        $user = $c; 
		    }
			/*print_r($user); */
			$stmt->close();
			return $user; 
		}
		else {
			return NULL;
		}
		
	}
	
	public function getUserById($uid) {
		
		$stmt = $this->conn->prepare("SELECT e.emp_id,e.emp_uid,e.emp_password,e.emp_name,e.emp_email,e.emp_type,e.emp_photo,e.branch_id,e.role_id,e.dept_id,e.emp_districts_assign,b.branch_name,r.role_name,d.dept_name FROM ".EMPLOYEES_TBL." as e JOIN ".BRANCHES_TBL." as b ON b.branch_id=e.branch_id JOIN ".ROLES_TBL." as r ON r.role_id=e.role_id JOIN ".DEPARTMENTS_TBL." as d ON d.dept_id=e.dept_id WHERE e.emp_id = ?");
		$stmt->bind_param("s", $uid);
		if ($stmt->execute()) {
			/*$user = $stmt->get_result()->fetch_assoc();
			$stmt->close();
			return $user;*/
			$user =array();
			$meta = $stmt->result_metadata(); 
			while ($field = $meta->fetch_field()) { 
				$params[] = &$row[$field->name]; 
			} 
			call_user_func_array(array($stmt, 'bind_result'), $params); 
			while ($stmt->fetch()) { 
				foreach($row as $key => $val) { 
					$c[$key] = $val; 
				} 
				$user = $c; 
			}
			/*print_r($user); */
			$stmt->close();
			return $user;
		}
		else {
			return NULL;
		}
		
	}
	public function getUserByEmpId($employee_id,$oldpassword) {
		
		$stmt = $this->conn->prepare("SELECT e.emp_password FROM ".EMPLOYEES_TBL." as e  WHERE e.emp_id = ?");
		$stmt->bind_param("s", $employee_id);
		$stmt->execute();
		$stmt->bind_result($password_hash);
		$stmt->store_result();
		if ($stmt->num_rows > 0) {
			//	Found user with the employee_id
			//	Now verify the password
			$user = $stmt->fetch();
			if (PassHash::check_password($password_hash, $oldpassword)) {
				//		User password is correct
				/*$user = $stmt->get_result()->fetch_assoc();*/
				$stmt->close();
				return $user;
			}
			else {
				//		user password is incorrect
				return FALSE;
			}
		}
		else {
			$stmt->close();
			//	user not existed with the employee_id
			return NULL;
		}
		
	}
	 
	/*OLD*/ 


	 /**     
	* Fetching user api key     
	* @param String $user_id user id primary key in user table     
	*/
	public function getApiKeyById($user_id) {
		$stmt = $this->conn->prepare("SELECT api_key FROM users WHERE id = ?");
		$stmt->bind_param("i", $user_id);
		if ($stmt->execute()) {
			$api_key = $stmt->get_result()->fetch_assoc();
			$stmt->close();
			return $api_key;
		}
		else {
			return NULL;
		}
	}
	
	/**     
	* Generating random Unique MD5 String for user Api key     
	*/
	private function generateApiKey() {
		return md5(uniqid(rand(), true));
	}
}

?>