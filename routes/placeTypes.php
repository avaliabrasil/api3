<?php
////// API.AVALIABRASIL.ORG/placeTypes
$app->get('/placeTypes', function() use ($app) {
	global $con;
	$con->connect();
	
	$sql = "SELECT id, name FROM category";

	$categories = executeQuery($con, $sql);
	foreach ($categories as $i => $l) {
		$cats[] = array(
			'id' => $l['id'],
			'name' => utf8_encode($l['name'])
		);
	}
	$categories = $cats;
		
	$sql = "SELECT id, name, id_category FROM place_type";
	$placeTypes = executeQuery($con, $sql);
	foreach ($placeTypes as $i => $l) {
		$pts[] = array(
			'id' => $l['id'],
			'name' => utf8_encode($l['name']),
			'idCategory' => $l['id_category']
		);
	}
	$placeTypes = $pts;

	$all = array(
		"categories"=>$categories,
    	"placeTypes"=>$placeTypes
    	);

	echo json_encode($all, JSON_UNESCAPED_UNICODE);
});






