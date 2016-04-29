<?php

$app->get('/ranking/{google_id}', function($google_id) use ($app) {
	global $con;
	$con->connect();
	
	$sql = "SELECT
	p.id, 
	p.name, 
	c.title as city,
	s.letter as state,
	cat.name as category,
	t.name as type
	FROM
		place p,
		city c,
		state s,
		category cat,
		place_type t

	WHERE
		p.google_id = '".$google_id."'

	AND
		p.id_city = c.id
	AND
		c.id_state = s.id
	AND
		p.id_type = t.id
	AND
		t.id_category = cat.id
	";
	
	$result = $con->query($sql);
    $result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
    $result = $result->fetchAll($result);



    foreach($result as $k=>$v)
    {
    	//print_r($v);
    	$data[] = array(
			"id" 				=> $v['id'],
			"name" 				=> utf8_encode($v['name']),
			"city" 				=> utf8_encode($v['city']),
			"state" 			=> $v['state'],
			"category" 			=> utf8_encode($v['category']),
			"type" 				=> utf8_encode($v['type']),
			"qualityIndex" 		=> array(3.8, 3.8, 3.8, 3.8, 3.8),
			"rankingPosition" 	=> array(
				"national" 	=> "2",
				"regional" 	=> "2",
				"state" 	=> "2",
				"municipal" => "2"
			),
			"rankingStatus" 	=> array(
				"national" 	=> "up",
				"regional" 	=> "up",
				"state" 	=> "down",
				"municipal" => "none"
			),
			"lastWeekSurveys" => "221",
			"comments" 	=> array(
				array(
					"uid"=>1,
					"description"=>"comentario",
					),
				array(
					"uid"=>1,
					"description"=>"comentario",
					),
				array(
					"uid"=>1,
					"description"=>"comentario",
					),

			),
		);
		
    }

	echo json_encode($data);

	
	



	// {
	// 	"id":3,
	// 	"name":"UPA Outra",
	// 	"city":"Porto Alegre",
	// 	"state":"RS",
	// 	"category":"Saude",
	// 	"type":"Pronto Atendimento",
	// 	"qualityIndex":[3.8, 3.8, 3.8, 2.5], //ultimos 6 meses
	// 	"rankingPosition":{
	// 		"national":2,
	// 		"regional":2,
	// 		"state":2,
	// 		"municipal":2
	// 	},
	// 	"rankingStatus":{
	// 		"national":"up",
	// 		"regional":"up",
	// 		"state":"down",
	// 		"municipal":"none"
	// 	},
	// 	"lastWeekSurveys":212,
	// 	"comments":[ //max 50
	// 		{"uid":1,"description":"teste de comentario"},
	// 		{"uid":1,"description":"teste de comentario"},
	// 		{"uid":1,"description":"teste de comentario"}
	// 	]
	// }
});








////// API.AVALIABRASIL.ORG/RANKING
$app->get('/ranking/{rankingtype}/{rankingvalue}', function($rankingtype, $rankingvalue) use ($app) {
	global $con;
	$con->connect();

	switch ($rankingtype) {
		case 'city':
			//city, state, region, category, type, googleid, daystosurveyexpire, previousrankingdays
			$sql = getRankingBy($rankingvalue);
			break;
		case 'state':
			$sql = getRankingBy('', $rankingvalue);
			break;
		case 'region':
			$sql = getRankingBy('', '', $rankingvalue);
			break;
		case 'category':
			$sql = getRankingBy('', '', '', $rankingvalue);
			break;
		case 'type':
			$sql = getRankingBy('', '', '', '', $rankingvalue);
			break;
		case 'googleid':
			$sql = getRankingBy('', '', '', '', '', $rankingvalue);
			break;
		case 'all':
			$sql = getRankingBy();
			break;
		
		default:
			$sql = getRankingBy();
			break;
	}

	$result = $con->query($sql);
    $result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
    $result = $result->fetchAll($result);

	echo json_encode($result);
});





