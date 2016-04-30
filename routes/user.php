<?php

$app->post('/authenticate', function() use ($app) {
	$data[] = array(
		'status'	=>200,
		'response'	=>array(
			'token' => 'faketoken',
			'expires' => 'expira_em'
		)
	);
	echo json_encode($data);
});