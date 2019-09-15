<?php

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use seeBattle\entities\Field;
use seeBattle\user_entities\User_Field;
use seeBattle\user_entities\Validator;
use seeBattle\entities\Ai;

require __DIR__ . '/vendor/autoload.php';

$userconn = pg_connect("host=localhost dbname=userships user=roman password=rimma");
$aiconn = pg_connect("host=localhost dbname=aiships user=roman password=rimma");

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

$app->post('/createUserShips', function($request, $response) use($userconn){
    $shipCoords = json_decode(file_get_contents('php://input'), true);

    $validator = new Validator();
    $validatedField = $validator->validate($shipCoords);

    if (isset($validatedField['error'])) {
        $response->getBody()->write(json_encode($validatedField));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    foreach ($shipCoords as $shipname => $points) {
        pg_query($userconn, "
            CREATE TABLE IF NOT EXISTS {$shipname}(
                y integer,
                x integer
            );
        ");
    }
    foreach ($shipCoords as $shipname => $points) {
        pg_query($userconn, "TRUNCATE {$shipname}");
        foreach ($points as $key => $point) {
            pg_insert($userconn, strtolower($shipname), $point);
        }
    }

    $userShips = [];
    foreach ($shipCoords as $shipname => $points) {
        $lowershipname = strtolower($shipname);
        $result = pg_query($userconn, "SELECT * FROM {$lowershipname}");
        $userShips[$shipname] = pg_fetch_all($result);
    }

    $response->getBody()->write("Ok");
    return $response;
});

$app->post('/usershooting', function($request, $response) use($aiconn, $userconn){
    $targetCoords = json_decode(file_get_contents('php://input'), true);
    $miss = '';
    $isShipAfloat = '';
    $sunkedShip = '';

    $result = pg_query($aiconn, "select pg_tables.tablename from pg_tables where schemaname='public';");
    $shipsNames = pg_fetch_all($result);

    $deletedItem = '';
    foreach ($shipsNames as $shipName) {
        $result = pg_query(
            $aiconn, "DELETE FROM {$shipName['tablename']} 
            WHERE y = {$targetCoords['y']} 
            AND x = {$targetCoords['x']} 
            RETURNING *;"
        );
        $deletedItem = pg_fetch_all($result);
        if ($deletedItem) {
            break;
        }
    }
    
    if (!$deletedItem) {
        $miss = $targetCoords;
    }

    $tablesNormalized = [];
    foreach ($shipsNames as $ship) {
        foreach ($ship as $key => $value) {
            $tablesNormalized[] = $value;
        }
    }
    $resultOfAiShooting = [];
    foreach ($tablesNormalized as $tableName) {
        $temp = pg_query($aiconn,"SELECT * FROM {$tableName};");
        $isShipAfloat = pg_fetch_all($temp);
        if (!$isShipAfloat) {
            $sunkedShip = $tableName;
            break;
        }
    }

    $aishipsUpdated = [];

    foreach ($shipsNames as $shipName) {
        $lowershipname = strtolower($shipName['tablename']);
        $result = pg_query($aiconn, "SELECT * FROM {$lowershipname}");
        $aishipsUpdated[$lowershipname] = pg_fetch_all($result);
    }

    $total = [
        'aishipsUpdated' => $aishipsUpdated,
        'deletedItem' => $deletedItem,
        'miss' => $miss,
        'isShipAfloat' => $isShipAfloat,
        'sunkedShip' => $sunkedShip
    ];

    $Encoded = json_encode($total);
    $response->getBody()->write($Encoded);
    return $response
            ->withHeader('Content-Type', 'application/json');
});

$app->post('/aishooting', function($request, $response) use($userconn) {
    $Ai = new Ai();
    $emptytablename = '';
    $isShipAfloat = '';
    //Выбор координат для выстрела
    $res = $Ai->shoot();
    
    //выбираем все таблицы из базы с кораблями пользователя
    $result = pg_query(
        $userconn, 
        "SELECT table_name FROM information_schema.tables 
        WHERE table_schema = 'public';"
    );
    //нормализация данных для обработки
    $tables = pg_fetch_all($result);
    $tablesNormalized = [];
    foreach (array_values($tables) as $value) {
        $tablesNormalized[] = $value['table_name'];
    }
    //проверка результата выстрела (попадание или промах)
    $resultOfAiShooting = [];
    foreach ($tablesNormalized as $tableName) {
        $responseFromDB = pg_query(
            $userconn, 
            "SELECT * FROM {$tableName} 
            WHERE x = {$res['x']} AND y = {$res['y']};"
        );
        if (pg_fetch_all($responseFromDB)) {
            $emptytablename = $tableName;
            $resultOfAiShooting[] = pg_fetch_all($responseFromDB);
            pg_query(
                $userconn, "DELETE FROM {$tableName} 
                WHERE y = {$res['y']} 
                AND x = {$res['x']};"
            );
            break;
        };
    }
    //отправка компьютеру ответа с результатом выстрела (попадание или промах)
    if (!empty($resultOfAiShooting)) {
        $res = pg_query(
            $userconn,
            "SELECT * FROM {$emptytablename};"
        );
        $isShipAfloat = pg_fetch_all($res);
        $Ai->takeResponseFromUser($resultOfAiShooting, $isShipAfloat);
    } else {
        $Ai->takeResponseFromUser($resultOfAiShooting);
    }
    //оновление данных по кораблям пользователя с учетом попаданий
    $resultingArray = [];
    foreach ($tablesNormalized as $tableName) {
        $temp = pg_query(
            $userconn,
            "SELECT * FROM {$tableName}"
        );
        $resultingArray[] = pg_fetch_all($temp);
    }

    $Encoded = json_encode(
        [
            'resultArr' => $resultingArray,
            'resOfShooting' => empty($resultOfAiShooting) ? $res : $resultOfAiShooting,
            'isShipAfloat' => $isShipAfloat,
            'sunkedShip' => $emptytablename
        ]
    );
    $response->getBody()->write($Encoded);
    return $response
        ->withHeader('Content-Type', 'application/json');
});

$app->run();