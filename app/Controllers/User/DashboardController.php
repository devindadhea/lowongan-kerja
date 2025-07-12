<?php

namespace App\Controllers\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController
{
    public function index(Request $request, Response $response)
    {
        session_start();
        $user = $_SESSION['user'] ?? null;

        if (!$user || $user['role'] !== 'user') {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        return view($response, 'user/dashboard.twig', ['user' => $user]);
    }
}
