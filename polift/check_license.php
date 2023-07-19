<?php
// sends back the number of seconds left of the trial period. 
// negative means trial period is over

require_once('secrets.php');
define('TRIAL_PERIOD_DAYS', 15);
define('TRIAL_PERIOD_SECONDS', TRIAL_PERIOD_DAYS * 86400);


$current_time = time();

function OnFailed() {
	echo 'FAIL';
	die();
}

if(isset($_GET['device_id'])) {
	$mysql_con = mysql_connect(DB_ADDRESS, DB_USER, DB_PASSWORD) or db_connect_error();
	mysql_select_db(DATABASE_NAME_POLIFT, $mysql_con);

	$device_id = mysql_real_escape_string($_GET['device_id']);
	$ip = mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
	//echo "INSERT INTO lookup_log VALUES('{$device_id}',{$current_time},'{$ip}')";
	mysql_query("INSERT INTO lookup_log VALUES('{$device_id}',{$current_time},'{$ip}')");

	mysql_query("INSERT INTO registrations VALUES('{$device_id}',{$current_time}) 
		ON DUPLICATE KEY UPDATE RegisterTime=RegisterTime");

	$result = mysql_query("SELECT RegisterTime FROM registrations WHERE DeviceID='{$device_id}'");

	if($result && ($row = mysql_fetch_array($result))) {
		$end_trial_time = $row['RegisterTime'] + TRIAL_PERIOD_SECONDS;
		echo ($end_trial_time - $current_time);
	} else {
		OnFailed();
	}
} else {
	OnFailed();
}

?>
