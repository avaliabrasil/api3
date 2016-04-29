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