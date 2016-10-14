<?php
function getUserId($deviceId) {
	global $con;
	$con->connect();

	$sql = "SELECT id from user where id_device='".$deviceId."'";
	$r = executeQuery($con, $sql);

	return $r;
}

$app->post('/authenticate', function() use ($app) {
	$post = $app->request->getJsonRawBody();
	if ($post->deviceId) {
		global $con;
		$con->connect();

		
		$r = getUserId($post->deviceId);
		
		if (sizeof($r) === 0) {
			$date = date("Y-m-d H:i:s");

			$sql = "INSERT INTO user (name, datetime, status, usertype, email, id_device)
			VALUES('John Doe', '".$date."', 1, 5, 'johndoe@avaliabrasil.org', '".$post->deviceId."')";

			executeQuery($con, $sql, false);
			$r = getUserId($post->deviceId);
		} 
		$data = array(
			'authorized' => true,
			'userId'	 => $r[0]['id']
		);
	} else {
		$data = array(
			'authorized' => false,
			'error'		 => "Err 0: No deviceId"
		);
	}
	echo json_encode($data);
});