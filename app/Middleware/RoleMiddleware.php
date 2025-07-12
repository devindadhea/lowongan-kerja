<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class RoleMiddleware
{
    protected $allowedRole;

    public function __construct(string $allowedRole)
    {
        $this->allowedRole = $allowedRole;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Cek session user
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== $this->allowedRole) {
            $response = new Response();
            $response->getBody()->write('403 - Akses ditolak');
            return $response->withStatus(403);
        }

        return $handler->handle($request);
    }
}
