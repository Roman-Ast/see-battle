<?php

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use seeBattle\entities\Field;
use seeBattle\user_entities\User_Field;
use seeBattle\user_entities\Validator;

require __DIR__ . '/vendor/autoload.php';

$userconn = pg_connect("host=localhost dbname=userships user=roman password=rimma");
$aiconn = pg_connect("host=localhost dbname=aiships user=roman password=rimma");
//pg_query($aiconn, '');


$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/templates');
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    return $this->get('renderer')->render($response, 'index.phtml');
});
$app->get('/field', function($request, $response) use($aiconn, $userconn){
    $field = new Field(10, 10);
    $battleField = $field->createBattleField();
    
    
    foreach ($battleField as $shipname => $points) {
        pg_query($aiconn, "
            CREATE TABLE IF NOT EXISTS {$shipname}(
                point integer,
                y integer,
                x integer
            );
        ");
    }
    foreach ($battleField as $shipname => $points) {
        pg_query($aiconn, "TRUNCATE {$shipname}");
        foreach ($points as $key => $point) {
            pg_insert($aiconn, strtolower($shipname), $point);
        }
    }
    $aiships = [];

    foreach ($battleField as $shipname => $points) {
        $lowershipname = strtolower($shipname);
        $result = pg_query($aiconn, "SELECT * FROM {$lowershipname}");
        $aiships[$shipname] = pg_fetch_all($result);
    }

    $total = ['aiships' => $aiships];
    $Encoded = json_encode($total);
    $response->getBody()->write($Encoded);
    return $response
            ->withHeader('Content-Type', 'application/json');
});

$app->post('/createUserShips', function($request, $response) {
    $shipCoords = json_decode(file_get_contents('php://input'), true);

    $validator = new Validator();
    $validated = $validator->validate($shipCoords);

    if (isset($validated['error'])) {
        $response->getBody()->write(json_encode($validated));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    $response->getBody()->write("Ok");
    return $response;
});

$app->post('/usershooting', function($request, $response) use($aiconn, $userconn){
    $targetCoords = json_decode(file_get_contents('php://input'), true);

    $y = $targetCoords['y'];
    $x = $targetCoords['x'];

    $result = pg_query($aiconn, "select pg_tables.tablename from pg_tables where schemaname='public';");
    $shipsNames = pg_fetch_all($result);

    foreach ($shipsNames as $shipName) {
        pg_query($aiconn, "DELETE FROM {$shipName['tablename']} WHERE y = {$y} AND x = {$x};");
    }

    $aishipsUpdated = [];

    foreach ($shipsNames as $shipName) {
        $lowershipname = strtolower($shipName['tablename']);
        $result = pg_query($aiconn, "SELECT * FROM {$lowershipname}");
        $aishipsUpdated[$lowershipname] = pg_fetch_all($result);
    }

    $Encoded = json_encode($aishipsUpdated);
    $response->getBody()->write($Encoded);
    return $response
            ->withHeader('Content-Type', 'application/json');
});















/*$app->get('/posts', function ($request, $response) use($posts){
    $page = $request->getQueryParams()['page'] ?? 1;
    $per = 5;
    $postsChunked = array_chunk($posts, $per);
    $params = [ 
        'posts' => $postsChunked,
        'page' => $page,
        'lengthOfPostsRepo' => count($postsChunked)
    ];
    return $this->get('renderer')->render($response, 'posts/index.phtml', $params);
});

$app->get('/posts/{slug}', function ($request, $response, $args) use($posts){
    $desiredPost = \collect($posts)->firstWhere('slug', $args['slug']);
    if (empty($desiredPost)) {
        $response->withStatus(404);
        return $this->get('renderer')->render($response, 'posts/error.phtml');
    }
    $params = [
        'desiredPost' => $desiredPost
    ];
    return $this->get('renderer')->render($response, 'posts/show.phtml', $params);
});
// END*/

$app->run();