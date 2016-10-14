<?php

$app->get('/ranking/{google_id}', function($google_id) use ($app) {
	global $con;
	$con->connect();
	
	$sql = "SELECT
	p.id, 
	p.name,
	p.address,
	c.title as city,
	c.id as id_city,
	s.letter as state,
	s.id as id_state,
	s.id_region as id_region,
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
	
	$result = executeQuery($con, $sql);

	$sql = getRankingGraph($google_id);
	$graph = executeQuery($con, $sql);


	$sql = getStatisticsByGoogleId($google_id);
	
	$ranking = executeQuery($con, $sql);
	$sql = "SELECT COUNT(s.id) as lastWeekSurveys from survey s
join place p
on s.id_place = p.id
WHERE 
s.date_time > DATE_ADD(NOW(), INTERVAL -7 DAY)
and
p.google_id = '".$google_id."'";
$lastWeekSurveys = executeQuery($con, $sql);


$sql = "

SELECT
ac.answer as description
FROM
answer_comment ac
join
survey_instrument si
on ac.id_surveyinstrument = si.id
join survey s
on si.id_survey = s.id

join place p
on s.id_place = p.id

where 
p.google_id = '".$google_id."'

order by ac.id limit 50";
$comments = executeQuery($con, $sql);
	$comms = array();
	foreach($comments as $k2=>$v2) {
		$comms[] = array("description" => utf8_encode($v2['description']));
	}


    foreach($result as $k=>$v)
    {
    	$data[] = array(
			"id" 				=> $v['id'],
			"id_city" 			=> $v['id_city'],
			"id_state" 			=> $v['id_state'],
			"id_region" 		=> $v['id_region'],
			"name" 				=> utf8_encode($v['name']),
			"address"			=> utf8_encode($v['address']),
			"city" 				=> utf8_encode($v['city']),
			"state" 			=> utf8_encode($v['state']),
			"category" 			=> utf8_encode($v['category']),
			"type" 				=> utf8_encode($v['type']),
			"qualityIndex" 		=> $graph,
			"rankingPosition" 	=> array(
				"national" 	=> $ranking[0]['ClassificacaoNacional'],
				"regional" 	=> $ranking[0]['ClassificacaoRegional'],
				"state" 	=> $ranking[0]['ClassificacaoEstadual'],
				"municipal" => $ranking[0]['ClassificacaoMunicipal']
			),
			"rankingStatus" 	=> array(
				"national" 	=> getDelta($ranking[0]['DeltaRankingNacional']),
				"regional" 	=> getDelta($ranking[0]['DeltaRankingRegional']),
				"state" 	=> getDelta($ranking[0]['DeltaRankingEstadual']),
				"municipal" => getDelta($ranking[0]['DeltaRankingMunicipal'])
			),
			"lastWeekSurveys" => $lastWeekSurveys[0]['lastWeekSurveys'],
			"comments" 	=> $comms
		);
		
    }

	echo json_encode($data, JSON_UNESCAPED_UNICODE);
});

////// API.AVALIABRASIL.ORG/RANKING
$app->get('/ranking', function() use ($app) {
	global $con;
	$con->connect();
	
	$sql = getRankingBy($_GET['idCity'], $_GET['idState'], $_GET['idRegion'], $_GET['idCategory'], $_GET['idType'], $_GET['googleId']);
    $result = executeQuery($con, $sql);

    $data = array();
    foreach ($result as $k => $v) {
    	$data['places'][] = array(
    		"googleId"=>$v['google_id'],
    		"rankingPosition"=>$v['rankingatual'],
    		"name"=>utf8_encode($v['name']),
    		"address"=>utf8_encode($v['address']),
    		"qualityIndex"=>$v['servperfatual']
    		);
    }
	echo json_encode($data, JSON_UNESCAPED_UNICODE);
});






