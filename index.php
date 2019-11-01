<?php

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use seeBattle\entities\Ai;
use seeBattle\src\Game;

require __DIR__ . '/vendor/autoload.php';

$container = new Container();
$container->set(
    'renderer', function () {
        return new \Slim\Views\PhpRenderer(__DIR__ . '/views');
    }
);

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    return $this->get('renderer')->render($response, 'index.phtml');
});

$app->get(
    '/getAiShips', function ($request, $response) {
        $game = new Game();
        $aiField = $game->getAiField();

        $Encoded = json_encode($aiField);

        $response->getBody()->write($Encoded);
        return $response
                ->withHeader('Content-Type', 'application/json');
    }
);

$app->post(
    '/createUserShips', function ($request, $response) {
        $shipCoordsFromUser = json_decode(file_get_contents('php://input'), true);

        $game = new Game();
        $userField = $game->checkUserField($shipCoordsFromUser);

        $Encoded = json_encode($userField);

        $response->getBody()->write($Encoded);
        return $response
                ->withHeader('Content-Type', 'application/json');
    }
);

$app->post(
    '/userShoot', function ($request, $response) {
        $userShotCoordinates = json_decode(file_get_contents('php://input'), true);

        $game = new Game();
        $aiFieldAfterUserShoot = $game->userStep($userShotCoordinates);

        if (!$aiFieldAfterUserShoot) {
            $response->getBody()->write(json_encode(['repeat' => 'this point has already been']));
            return $response
                    ->withHeader('Content-Type', 'application/json');
        }

        $Encoded = json_encode($aiFieldAfterUserShoot);
        $response->getBody()->write($Encoded);
        return $response
                ->withHeader('Content-Type', 'application/json');
    }
);

$app->post(
    '/aishooting', function ($request, $response) {
        $game = new Game();
        $userFieldAfterAiShoot = $game->aiStep();

        $Encoded = json_encode($userFieldAfterAiShoot);
        $response->getBody()->write($Encoded); 

        return $response
            ->withHeader('Content-Type', 'application/json');
    }
);

$app->run();