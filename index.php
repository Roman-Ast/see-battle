<?php

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use seeBattle\entities\Field;
use seeBattle\user_entities\User_Field;
use seeBattle\user_entities\Validator;

require __DIR__ . '/vendor/autoload.php';

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
$app->get('/field', function($request, $response) {
    $field = new Field(10, 10);
    $battleField = $field->createBattleField();
    
    $halo = $field->getHalo();
    $fieldempty = $field->createField();
    $total = ['halo' => $halo, 'fieldempty' => $fieldempty, 'battleField' => $battleField];
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