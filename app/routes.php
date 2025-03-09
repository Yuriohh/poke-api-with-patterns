<?php

use App\Middlewares\AuthMiddleware;
use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write('Hello World!');
    return $response;
})->add(new AuthMiddleware);

$app->get('/auth', function (Request $request, Response $response, $args) {
    $payload = [
        'iss' => $_SERVER['HTTP_HOST'],
        'aud' => 'localhost',
        'iat' => time(),
        'exp' => time() + (24 * 3600), //24h
    ];

    try {
        $jwt = JWT::encode($payload, $_ENV['SECRET_KEY'], 'HS256');
        $response->getBody()->write(json_encode([
            'token' => $jwt,
            'expiredAt' => date('Y-m-d H:i:s', $payload['exp']),
        ]));

        return $response->withStatus(201);
    } catch (UnexpectedValueException $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(400);
    }
});

$app->run();
