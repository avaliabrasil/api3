<?php

function canEvaluate($id_place, $user_id) {
	global $con;
	$con->connect();

	$sql_evaluations = "SELECT count(id) as evaluations from survey where id_place = ".$id_place." AND id_user = ".$user_id." AND date_time >= '".date("Y-m-d H:i:s", $yesterday)."'";
	$evaluations = executeQuery($con, $sql_evaluations);

	if ($evaluations[0]['evaluations'] > 0) {
		$data[] = array(
			"status" => 200,
			"response" => array(
				"authorized" => false,
				"error" => "Você já avaliou este local nas últimas 24 horas. Você pode avaliar um estabelecimento no máximo uma vez por dia."
				)
			);
	} else {
		$data[] = array(
			"status" => 200,
			"response" => array(
				"authorized" => true,
				"error" => ""
				)
			);
	}
	return $data;
}

function getDelta($value) {
	if ($value < 0)
		return 'down';
	else if ($value > 0)
		return 'up';
	else
		return 'none';
}

function executeQuery($con, $sql, $assoc_array = true) {
	//echo $sql . '<br><br>';
	$r = $con->query($sql);
	if ($assoc_array) {
	    $r->setFetchMode(Phalcon\Db::FETCH_ASSOC);
	    $r = $r->fetchAll($r);
    }
    return $r;
}
function getNamePlace($googleId) {
	global $con;
	$con->connect();

	$sql = "SELECT name FROM place WHERE google_id='".$googleId."'";
	return executeQuery($con, $sql);
}
function getCityId($cityName, $stateLetter) {
	global $con;
	$con->connect();


	$sql = "
	SELECT c.id FROM
	city c
	join state s
	on c.id_state = s.id
	WHERE
	c.title = '".utf8_decode($cityName)."'
	AND
	s.letter = '".$stateLetter."'";
	return executeQuery($con, $sql);
}
function getIdPlace($googleId) {
	global $con;
	$con->connect();

	$sql = "SELECT id FROM place WHERE google_id='".$googleId."'";
	return executeQuery($con, $sql);
}
function getAnswerType($question_type) {
	global $con;
	$con->connect();
	$sql = "SELECT is_likert, is_numeric, is_comment FROM question_type WHERE id=".$question_type;
	$r = executeQuery($con, $sql);

	if ($r[0]['is_likert'])
		return 'likert';

	if ($r[0]['is_numeric'])
		return 'number';

	if ($r[0]['is_comment'])
		return 'comment';
}

function pr($s) {
	echo "<pre>";
	print_r($s);
	echo "</pre>";
}