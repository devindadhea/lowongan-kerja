<?php

namespace App\Controllers\Admin;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Medoo\Medoo;

class DashboardController
{
    
    protected $view;
    protected $db;

    public function __construct(Twig $view, Medoo $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    public function index(Request $request, Response $response, $args)
    {
        $totalLowongan = $this->db->count('jobs');
        $totalUser = $this->db->count('users', ['role' => 'user']);
        $totalPelamar = $this->db->count('applications');

        return $this->view->render($response, 'admin/dashboard.twig', [
            'totalLowongan' => $totalLowongan,
            'totalUser' => $totalUser,
            'totalPelamar' => $totalPelamar,
            'session' => $_SESSION
        ]);
    }
}
