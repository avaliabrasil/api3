<?php

function executeQuery($con, $sql, $assoc_array = true) {
	$r = $con->query($sql);
	if ($assoc_array) {
	    $r->setFetchMode(Phalcon\Db::FETCH_ASSOC);
	    $r = $r->fetchAll($r);
    }
    return $r;
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
	c.title = '".$cityName."'
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
		return 'text';
}

function pr($s) {
	echo "<pre>";
	print_r($s);
	echo "</pre>";
}