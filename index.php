<?php 
use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Http\Response;


// Use Loader() to autoload our model
$loader = new Loader();

$loader->registerDirs(
    array(
        __DIR__ . '/models/'
    )
)->register();

$di = new FactoryDefault();

// Set up the database service
$di->set('db', function () {
    return new PdoMysql(
        array(
            "host"     => "127.0.0.1",
            "username" => "root",
            "password" => "root",
            "dbname"   => "avaliabrasil2"
        )
    );
});

// Create and bind the DI to the application
$app = new Micro($di);


if ($_SERVER['SERVER_NAME'] == 'localhost') {
	include('config/development.php');
} else {
	include('config/production.php');
}
include('config/helper.php');
include('queries/queries.php');
include('routes/user.php');
include('routes/ranking.php');
include('routes/survey.php');
include('routes/locations.php');

    
	


// Retrieves all places
$app->get('/places', function () use ($app) {

    $phql = "SELECT id, name, google_id FROM place ORDER BY name";
    $places = $app->modelsManager->executeQuery($phql);

    $data = array();
    foreach ($places as $place) {
        $data[] = array(
            'id'   => $place->id,
            'name' => $place->name,
            'google_id' => $place->google_id,
        );
    }
    echo json_encode($data);
});



// // Retrieves all available instruments
// $app->get('/survey/{google_id}', function () use ($app) {
// 	$phql = "SELECT id, status FROM instrument";
// 	$instruments = $app->modelsManager->executeQuery($phql);

// 	$data = array();
// 	foreach ($instruments as $k=>$v) {
// 		$phql = "SELECT id, status FROM instrument";
// 		$instruments = $app->modelsManager->executeQuery($phql);




// 		$data[] = array(
// 			'id'   => $v->id,
// 			'data' => $data2
// 		);

// 	}
// 	echo json_encode($data);
// });














$app->handle();

