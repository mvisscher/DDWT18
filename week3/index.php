<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Include model.php */
include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt18_week3', 'ddwt18', 'ddwt18');

$cred = set_cred("ddwt18","ddwt18");
/* Create Router instance */
$router = new \Bramus\Router\Router();

$router->before('GET|POST|PUT|DELETE', '/api/.*', function() use($cred){
    if (!check_cred($cred)){
        echo json_encode('Authentication required.');
        http_response_code(401);
        die();
    }

});

// Add routes here
$router->mount('/api', function() use ($router, $db){
    /* GET for reading all series */
    $router->get('/series', function() use($db) {
        echo json_encode(get_series($db));
    });
    /* GET for reading individual series */
    $router->get('/series/(\d+)', function($id) use($db) {
        echo json_encode(get_serieinfo($db,$id));
    });
    $router->delete('/series/(\d+)', function($id) use($db) {
        echo json_encode(remove_serie($db,$id));
    });
    $router->post('/series/', function() use($db) {
        echo json_encode(add_serie($db, $_POST));
    });
    $router->put('/series/(\d+)', function($id) use($db) {
        $_PUT = array();
        parse_str(file_get_contents('php://input'), $_PUT);
        $serie_info = $_PUT + ["serie_id" => $id];
        echo json_encode(update_serie($db, $serie_info));
    });

});

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    echo 'The page you requested could not be found';
});

/* Run the router */
$router->run();
