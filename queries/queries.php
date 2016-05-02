<?php
function getRankingBy($cityid = '', $stateid = '', $regionid = '', $categoryid = '', $typeid = '', $google_id = '', $daystosurveyexpire = 180, $previousrankingdays = 30) {
	$where = ' ';

	if ($cityid)
		$where .= 'AND ct.id = "' .$cityid. '"';

	if ($stateid)
		$where .= 'AND st.id = "' .$stateid. '"';
	
	if ($regionid)
		$where .= 'AND re.id = "' .$regionid. '"';

	if ($typeid)
		$where .= 'AND pt.id = "' .$typeid. '"';

	if ($categoryid)
		$where .= 'AND c.id = "' .$categoryid. '"';

	if ($google_id)
		$where .= 'AND p.google_id = "' .$google_id. '"';


	$sql = "

SELECT
	@rankingatual:=@rankingatual+1 as rankingatual,
	servperfatual,
	name,
	address,
	google_id
	
	FROM

(
	SELECT 
	
	100*
		(
			(
				SUM(
				case q.is_negative
					when 0 then al.answer
					else (mi.likertpoints + 1) - al.answer
				end) /
				COUNT(al.id)
			) - 1
		) / (mi.likertpoints - 1) as  servperfatual,
	
	p.id,
	p.google_id,
	p.address,
	p.name
	
	
	
	
FROM

	answer_likert al join
	survey_instrument si on al.id_surveyinstrument = si.id join
	survey s on si.id_survey = s.id join
	place p on s.id_place = p.id join
	question q on al.id_question = q.id join
	instrument i on si.id_instrument = i.id join
	masterinstrument mi on i.id_masterinstrument = mi.id join
	city ct on p.id_city = ct.id join
	state st on ct.id_state = st.id join
	region re on st.id_region = re.id join
	place_type pt on p.id_type = pt.id join
	category c on pt.id_category = c.id

WHERE

	
	si.id_instrument = 1 /*Apenas servperf por enquanto*/
	AND
	
	s.date_time > DATE_ADD(NOW(), INTERVAL -".$daystosurveyexpire." DAY) /*Apenas preenchidos nos últimos 180 dias*/
	
	
	".$where."

GROUP BY p.id

ORDER BY servperfatual DESC

) 
AS scoresatuais,

(SELECT @rankingatual:=0) r

";
	return $sql;

}
function getRankingBy_old($cityid = '', $stateid = '', $regionid = '', $categoryid = '', $typeid = '', $google_id = '', $daystosurveyexpire = 180, $previousrankingdays = 30) {
	$where = ' ';

	if ($cityid)
		$where .= 'AND ct.id = "' .$cityid. '"';

	if ($stateid)
		$where .= 'AND st.id = "' .$stateid. '"';
	
	if ($regionid)
		$where .= 'AND re.id = "' .$regionid. '"';

	if ($typeid)
		$where .= 'AND pt.id = "' .$typeid. '"';

	if ($categoryid)
		$where .= 'AND c.id = "' .$categoryid. '"';

	if ($google_id)
		$where .= 'AND p.google_id = "' .$google_id. '"';


	$sql = "
	SELECT

	rankingatual.google_id,
	rankingatual.id,
	rankingatual.rankingatual,
	rankingatual.servperfatual,
	rankinganterior.rankinganterior,
	rankinganterior.servperfanterior,
	rankinganterior.rankinganterior - rankingatual.rankingatual as deltaranking,
	rankingatual.servperfatual - rankinganterior.servperfanterior as deltaServperf

	FROM

	(
	SELECT
	@rankingatual:=@rankingatual+1 as rankingatual,
	servperfatual,
	id,
	google_id

	FROM

	(
	SELECT 

	100*
		(
			(
				SUM(
				case q.is_negative
					when 0 then al.answer
					else (mi.likertpoints + 1) - al.answer
				end) /
				COUNT(al.id)
			) - 1
		) / (mi.likertpoints - 1) as  servperfatual,

	p.id,
	p.google_id



	FROM

	answer_likert al join
	survey_instrument si on al.id_surveyinstrument = si.id join
	survey s on si.id_survey = s.id join
	place p on s.id_place = p.id join
	question q on al.id_question = q.id join
	instrument i on si.id_instrument = i.id join
	masterinstrument mi on i.id_masterinstrument = mi.id join
	city ct on p.id_city = ct.id join
	state st on ct.id_state = st.id join
	region re on st.id_region = re.id join
	place_type pt on p.id_type = pt.id join
	category c on pt.id_category = c.id

	WHERE


	si.id_instrument = 1
	AND

	s.date_time > DATE_ADD(NOW(), INTERVAL -".$daystosurveyexpire." DAY)

	".$where."

	GROUP BY p.id
	ORDER BY servperfatual DESC)
	AS scoresatuais,
	(SELECT @rankingatual:=0) r) 
	AS rankingatual
	LEFT JOIN
	(SELECT
	@rankinganterior:=@rankinganterior+1 as rankinganterior,
	servperfanterior,
	id,
	google_id
	FROM
	(SELECT 
	100*
		(
			(
				SUM(
				case q.is_negative
					when 0 then al.answer
					else (mi.likertpoints + 1) - al.answer
				end) /
				COUNT(al.id)
			) - 1
		) / (mi.likertpoints - 1) as  servperfanterior,
	p.id,
	p.google_id	
	FROM
	answer_likert al join
	survey_instrument si on al.id_surveyinstrument = si.id join
	survey s on si.id_survey = s.id join
	place p on s.id_place = p.id join
	question q on al.id_question = q.id join
	instrument i on si.id_instrument = i.id join
	masterinstrument mi on i.id_masterinstrument = mi.id join
	city ct on p.id_city = ct.id join
	state st on ct.id_state = st.id join
	region re on st.id_region = re.id join
	place_type pt on p.id_type = pt.id join
	category c on pt.id_category = c.id
	WHERE
	si.id_instrument = 1 /*Apenas servperf por enquanto*/
	AND	
	s.date_time BETWEEN
		DATE_ADD(NOW(), INTERVAL - (".$daystosurveyexpire."+".$previousrankingdays.") DAY)
		AND
		DATE_ADD(NOW(), INTERVAL - ".$previousrankingdays." DAY)
	".$where."
	GROUP BY p.id
	ORDER BY servperfanterior DESC) 
	AS scoresanteriores,
	(SELECT @rankinganterior:=0) r) 
	AS rankinganterior on rankingatual.id = rankinganterior.id
	";
	return $sql;

}


function getQualityIndexByGoogleId($google_id, $survey_id = '', $daystosurveyexpire = 180) {
	$sql = "


	SELECT 
	
	100*
		(
			(
				SUM(
				case q.is_negative
					when 0 then al.answer
					else (mi.likertpoints + 1) - al.answer
				end) /
				COUNT(al.id)
			) - 1
		) / (mi.likertpoints - 1) as servperfatual,
	
	p.id,
	p.google_id
	
	
	
FROM

	answer_likert al join
	survey_instrument si on al.id_surveyinstrument = si.id join
	survey s on si.id_survey = s.id join
	place p on s.id_place = p.id join
	question q on al.id_question = q.id join
	instrument i on si.id_instrument = i.id join
	masterinstrument mi on i.id_masterinstrument = mi.id join
	city ct on p.id_city = ct.id join
	state st on ct.id_state = st.id join
	region re on st.id_region = re.id join
	place_type pt on p.id_type = pt.id join
	category c on pt.id_category = c.id

WHERE

	p.google_id = '".$google_id."'  and
	si.id_instrument = 1 
	AND
	
	s.date_time > DATE_ADD(NOW(), INTERVAL - ".$daystosurveyexpire." DAY)";

	if ($survey_id)
		$sql .= " and s.id = ".$survey_id;

	return $sql;
}

function getQualityIndexRankingByGoogleId($google_id, $daystosurveyexpire = 180, $previousrankingdays = 30) {
	global $con;
	$sql = "SELECT p.id_type from place p where p.google_id = '".$google_id."'";
	$typeid = executeQuery($con, $sql);
	$typeid = $typeid[0]['id_type'];


	$sql = "SELECT p.id_city from place p where p.google_id = '".$google_id."'";
	$cityid = executeQuery($con, $sql);
	$cityid = $cityid[0]['id_city'];

	$sql = "SELECT c.id_state FROM city c where c.id = ".$cityid;
	$stateid = executeQuery($con, $sql);
	
	$stateid = $stateid[0]['id_state'];


	$sql = "
SELECT 

	rankingatual.google_id,
	rankingatual.id,
	rankingatual.servperfatual as IndicedeQualidade,
	rankingatual.servperfatual - rankinganterior.servperfanterior as DeltaIndicedeQualidade,
	rankingatual.rankingestadualatual as ClassificacaoEstadual,
	rankingatual.rankingestadualatual - rankinganterior.rankingestadualanterior as DeltaRankingEstadual


FROM

	(
	SELECT
		@rankingestadualatual:=@rankingestadualatual+1 as rankingestadualatual,
		servperfatual,
		id,
		google_id
		
	FROM
	
	(
		SELECT 
		
			100*
				(
					(
						SUM(
						case q.is_negative
							when 0 then al.answer
							else (mi.likertpoints + 1) - al.answer
						end) /
						COUNT(al.id)
					) - 1
				) / (mi.likertpoints - 1) as  servperfatual,
			
			p.id,
			p.google_id
		
		
		FROM
	
			answer_likert al join
			survey_instrument si on al.id_surveyinstrument = si.id join
			survey s on si.id_survey = s.id join
			place p on s.id_place = p.id join
			question q on al.id_question = q.id join
			instrument i on si.id_instrument = i.id join
			masterinstrument mi on i.id_masterinstrument = mi.id join
			city ct on p.id_city = ct.id join
			state st on ct.id_state = st.id join
			region re on st.id_region = re.id join
			place_type pt on p.id_type = pt.id join
			category c on pt.id_category = c.id
	
		WHERE
	
			si.id_instrument = 1 /*Apenas servperf por enquanto*/
			AND
			
			s.date_time > DATE_ADD(NOW(), INTERVAL -".$daystosurveyexpire." DAY) /*Apenas preenchidos nos últimos 180 dias*/
			
			AND
			
			p.id_type = ".$typeid."
			
			AND
			
			ct.id_state = ".$stateid."
			
	
	GROUP BY p.id
	
	ORDER BY servperfatual DESC
	
	) 	AS scoresatuais,
	
	(SELECT @rankingestadualatual:=0) r
	
	) 
	AS rankingatual
	
	LEFT JOIN
	
	(
	SELECT
		@rankingestadualanterior:=@rankingestadualanterior+1 as rankingestadualanterior,
		servperfanterior,
		id,
		google_id
		
		FROM
	
	(
		SELECT 
		
		100*
			(
				(
					SUM(
					case q.is_negative
						when 0 then al.answer
						else (mi.likertpoints + 1) - al.answer
					end) /
					COUNT(al.id)
				) - 1
			) / (mi.likertpoints - 1) as  servperfanterior,
		
		p.id,
		p.google_id
		
		
		
	FROM
	
		answer_likert al join
		survey_instrument si on al.id_surveyinstrument = si.id join
		survey s on si.id_survey = s.id join
		place p on s.id_place = p.id join
		question q on al.id_question = q.id join
		instrument i on si.id_instrument = i.id join
		masterinstrument mi on i.id_masterinstrument = mi.id join
		city ct on p.id_city = ct.id join
		state st on ct.id_state = st.id join
		region re on st.id_region = re.id join
		place_type pt on p.id_type = pt.id join
		category c on pt.id_category = c.id
	
	WHERE
	
		si.id_instrument = 1 /*Apenas servperf por enquanto*/
		AND
		
		s.date_time BETWEEN
			DATE_ADD(NOW(), INTERVAL - (".$daystosurveyexpire."+".$previousrankingdays.") DAY)
			AND
			DATE_ADD(NOW(), INTERVAL - ".$previousrankingdays." DAY)
		
		AND
			
			p.id_type = ".$typeid."

		AND
			
			ct.id_state = ".$stateid."
			
	
	GROUP BY p.id
	
	ORDER BY servperfanterior DESC
	
	) 
	AS scoresanteriores,
	
	(SELECT @rankingestadualanterior:=0) r
	
	) 
	AS rankinganterior
	
	on rankingatual.id = rankinganterior.id
WHERE 
	rankingatual.google_id = '".$google_id."'


	";
	return $sql;
}


function getRankingGraph($google_id, $earliermonths = 6) {
	$sql = "





SELECT 

		CONCAT(YEAR(s.date_time),'.',MONTH(s.date_time)) as month,		
		100*
			(
				(
					SUM(
					case q.is_negative
						when 0 then al.answer
						else (mi.likertpoints + 1) - al.answer
					end) /
					COUNT(al.id)
				) - 1
			) / (mi.likertpoints - 1) as value
		

	FROM
	
		answer_likert al join
		survey_instrument si on al.id_surveyinstrument = si.id join
		survey s on si.id_survey = s.id join
		place p on s.id_place = p.id join
		question q on al.id_question = q.id join
		instrument i on si.id_instrument = i.id join
		masterinstrument mi on i.id_masterinstrument = mi.id
	
	WHERE
	
			si.id_instrument = 1 /*Apenas servperf por enquanto*/
		AND
			p.google_id = '".$google_id."'
		AND
		
		s.date_time > 
		
		DATE_SUB(
				
				DATE_SUB(CURRENT_DATE(), INTERVAL DAYOFMONTH(CURRENT_DATE()) DAY ),
				
				INTERVAL ".$earliermonths."-1 MONTH)
	
	GROUP BY p.id, month
	
	ORDER BY YEAR(s.date_time), MONTH(s.date_time)
	


	";
	return $sql;
}

