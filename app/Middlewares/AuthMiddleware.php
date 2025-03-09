<?php

namespace App\Middlewares;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use UnexpectedValueException;

class AuthMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        JWT::$leeway = 10800;
        try {
            $decoded = JWT::decode($request->getHeaderLine('Authorization'), new Key($_ENV['SECRET_KEY'], 'HS256'));

            return $handler->handle($request);
        } catch (UnexpectedValueException $e) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(401);
        }
    }
}
