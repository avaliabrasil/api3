<?php

$app->post('/authenticate', function() use ($app) {
	global $con;
	$con->connect();

	$post = $app->request->getJsonRawBody();
	$sql = "SELECT id from user where id_device='".$post->deviceId."'";
	$r = executeQuery($con, $sql);
	
	if (sizeof($r) === 0) {
		$data = array(
			'authorized' => false,
			'error'	 	 => "Não foi possível efetuar o acesso. Tente novamente mais tarde."
		);		
	} else {
		$data = array(
			'authorized' => true,
			'userId'	 => $r[0]['id']
		);
	}
	echo json_encode($data);
});