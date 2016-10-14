<?php
////// API.AVALIABRASIL.ORG/locations
$app->get('/locations', function() use ($app) {
	global $con;
	$con->connect();
	
	$sql = getLocations();
    $result = executeQuery($con, $sql);

    $data = array();
    foreach ($result as $k => $v) {
    	$data[] = array(
    		"idWeb"=>$v['id_web'],
    		"type"=>$v['type'],
    		"description"=>utf8_encode($v['description'])
    		);
    	
    }
	echo json_encode($data, JSON_UNESCAPED_UNICODE);
});






