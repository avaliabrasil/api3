<?php

//- GET api.avaliabrasil.org/survey/PLACE_ID
$app->get('/survey/{google_id}', function($google_id) use ($app) {
	global $con;
	$con->connect();
	

	

	
	$categories = array();
	$placeTypes = array();
	$rankingStatus = array();
	$qualityIndexStatus = array();
	$qualityIndex = '';
	$rankingPosition = '';



	$sql = "SELECT google_id FROM place where google_id = '".$google_id."'";
	$result = executeQuery($con, $sql);
	
	
    if (!sizeof($result)) {
    	$newPlace = true;
    	
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
		foreach ($categories as $i => $l) {
			$pts[] = array(
				'id' => $l['id'],
				'name' => utf8_encode($l['name']),
				'id_category' => $l['id_category']
			);
		}
		$placeTypes = $pts;
		

    } else {
    	$newPlace = false;
    	$sql = getQualityIndexRankingByGoogleId($google_id);
		
		$r = executeQuery($con, $sql);
		
		$qualityIndex = $r[0]['IndicedeQualidade'];
		$rankingPosition = $r[0]['ClassificacaoEstadual'];
		$rankingStatus = getDelta($r[0]['DeltaRankingEstadual']);
		$qualityIndexStatus = getDelta($r[0]['DeltaIndicedeQualidade']);

    	//$sql = getQualityIndexByGoogleId($google_id);
    	//$ = executeQuery($con, $sql);
    }
    	$sql = "SELECT id FROM instrument where id_masterinstrument=1"; //apenas servperf

    	$instruments = executeQuery($con, $sql);

    	$data = array();
    	$i_instrument = 0;
    	foreach ($instruments as $k => $v) {

    		$sql = "SELECT id, group_order FROM item_group WHERE id_masterinstrument = ".$v['id']." order by group_order ASC";
    		$groups = executeQuery($con, $sql);
    		$i_groups = 0;


    		foreach ($groups as $k2 => $v2) {
    			$sql = "SELECT id, title, id_type from question where id_group = " . $v2['id'];
    			$questions = executeQuery($con, $sql);
    			$i_questions = 0;
    			foreach ($questions as $k3 => $v3) {
				 	$group[$i_groups]["id"] = $v2['id'];
				 	$group[$i_groups]["order"] = $v2['group_order'];
				 	
				 	$group[$i_groups]["questions"][$i_questions] = array(
				 	  	"id" 			=> $v3['id'],
				 	  	"title" 		=> utf8_encode($v3['title']),
				 	  	"questionType" 	=> getAnswerType($v3['id_type'])
				 	);
				 	$i_questions++;
				}
				$i_groups++;
    		}
    		



    		$data[$i_instrument] = array(
    			"instruments" => array(
    				array(
    				"id"=>$v['id'],
    				"groups"=>$group,
    				)
    				),
    			"newPlace"=>$newPlace,
    			"qualityIndex"=>$qualityIndex,
    			"rankingPosition"=>$rankingPosition,
    			"rankingStatus"=>$rankingStatus,
    			"qualityIndexStatus"=>$qualityIndexStatus,
    			"categories"=>$categories,
    			"placeTypes"=>$placeTypes

    		);
    		$i_instrument++;
    	}
    echo json_encode($data);
});


//- POST api.avaliabrasil.org/survey/PLACE_ID
$app->post('/survey/{google_id}', function($google_id) use ($app) {
	global $con;
	$con->connect();


	$post = $app->request->getJsonRawBody();

	if ($post->newPlace) {

    	$date = date("Y-m-d H:i:s");
    	$id_city = getCityId($post->cityName, $post->stateLetter);
    	$id_city = $id_city[0]['id'];

    	$sql_insert = "INSERT INTO place (id_type, name, address, created_at, updated_at, status, id_city, google_id)
    	VALUES('".$post->placeTypeId."', '".$post->name."', '".$post->address."', '".$date."', '".$date."', 1, ".$id_city.", '".$google_id."')";
    	
    	$r = executeQuery($con, $sql_insert, false);
	}

	$date = date("Y-m-d H:i:s");
	$id_place = getIdPlace($google_id);
	$id_place = $id_place[0]['id'];
	$sql = "insert into survey (date_time, id_user, id_place, status)
	VALUES('".$date."', ".$post->userId.", ".$id_place.", 1)
	";


	executeQuery($con, $sql, false);

	$id_survey = $con->lastInsertId();
	$sql = "insert into survey_instrument (id_survey, id_instrument) 
	VALUES(".$id_survey.", 1)";
	executeQuery($con, $sql, false);

	$id_survey_instrument = $con->lastInsertId();

	foreach ($post->answers as $key => $value) {
		$sql = "insert into answer_".$value->questionType." (id_surveyinstrument, id_question, answer)
		VALUES(".$id_survey_instrument.", ".$value->questionId.", '".$value->answer."')";
		
		executeQuery($con, $sql, false);
	}



	$sql = getQualityIndexByGoogleId($google_id, $id_survey);
	$r = executeQuery($con, $sql);
	$myQI = $r[0]['servperfatual'];
	
	$sql = getQualityIndexByGoogleId($google_id);
	$r = executeQuery($con, $sql);

	$globalQI = $r[0]['servperfatual'];

	$placeName = getNamePlace($google_id);
	
	$data[] = array(
		"status" => 200,
		"response" => array(
			"fbShareText" => "Eu avaliei o local: ".$placeName[0]['name'].". Na minha avaliação, o Índice de Qualidade deste local é ".$myQI.". O Índice de Qualidade Atual é ".$globalQI.". Baixe o aplicativo Avalia Brasil e avalie também."
			)
		);
	echo json_encode($data);
});