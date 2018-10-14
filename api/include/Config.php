<?php
/** Database config */
define('DB_USERNAME', 'root');  
define('DB_PASSWORD', '');  
define('DB_HOST', 'localhost');  
define('DB_NAME', 'bspdhyd_wp1');
/** Debug modes */
define('PHP_DEBUG_MODE',true);  
define('SLIM_DEBUG',true); 
/** Tables **/
define('USERS_TBL','bspd_test_users');
define('GOTRAS_TBL','BSPD_Gotras');
define('DOJ_TBL','bspd_doj_master');
define('LOC_TBL','bspd_location_master');
define('EVENTS_TBL','BSPD_Event');
define('EVENTS_TBL_REGIS','BSPD_Event_Registration');

/** **/
define('CREATED_SUCCESSFULLY', 0);  
define('CREATE_FAILED', 1);  
define('ALREADY_EXISTED', 2);

?> 